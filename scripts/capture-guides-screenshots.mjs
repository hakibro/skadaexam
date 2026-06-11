import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import puppeteer from 'puppeteer';

const rootDir = process.cwd();
const guidePath = path.join(rootDir, 'resources', 'guides', 'skadaexam-guides.json');
const baseUrl = (process.env.GUIDE_BASE_URL || 'http://skadaexam.test').replace(/\/$/, '');
const outputRoot = path.join(rootDir, 'public');
const rolesFilter = (process.env.GUIDE_ROLES || '')
  .split(',')
  .map((role) => role.trim())
  .filter(Boolean);
const validateOnly = process.argv.includes('--validate');

const defaultCredentials = {
  admin: { loginPath: '/login', email: 'admin@skadaexam.test', password: 'password123' },
  data: { loginPath: '/login', email: 'data.guru@skadaexam.test', password: 'password123' },
  naskah: { loginPath: '/login', email: 'naskah.guru@skadaexam.test', password: 'password123' },
  ruangan: { loginPath: '/login', email: 'ruangan.guru@skadaexam.test', password: 'password123' },
  pengawas: { loginPath: '/login', email: 'pengawas.guru@skadaexam.test', password: 'password123' },
  koordinator: { loginPath: '/login', email: 'koordinator.guru@skadaexam.test', password: 'password123' },
  siswa: {
    loginPath: '/login/siswa',
    idyayasan: process.env.GUIDE_SISWA_IDYAYASAN || '',
    token: process.env.GUIDE_SISWA_TOKEN || '',
  },
};

const envCredentials = parseJsonEnv('GUIDE_CREDENTIALS_JSON', {});
const urlOverrides = parseJsonEnv('GUIDE_URL_OVERRIDES_JSON', {});
const credentials = deepMerge(defaultCredentials, envCredentials);

const guideSource = JSON.parse(await fs.readFile(guidePath, 'utf8'));
validateGuideSource(guideSource);

if (validateOnly) {
  console.log(`Guide JSON valid: ${guideSource.guides.length} roles`);
  process.exit(0);
}

const browser = await puppeteer.launch({
  headless: process.env.GUIDE_HEADLESS !== 'false',
  defaultViewport: {
    width: Number(process.env.GUIDE_VIEWPORT_WIDTH || 1366),
    height: Number(process.env.GUIDE_VIEWPORT_HEIGHT || 768),
  },
});

try {
  for (const guide of guideSource.guides) {
    if (rolesFilter.length > 0 && !rolesFilter.includes(guide.role)) {
      continue;
    }

    const page = await browser.newPage();
    page.setDefaultTimeout(Number(process.env.GUIDE_TIMEOUT_MS || 20000));

    const loggedIn = await loginForRole(page, guide.role);

    for (const section of guide.sections) {
      for (const step of section.steps) {
        const resolvedUrl = urlOverrides[step.id] || step.url;

        if (hasPlaceholder(resolvedUrl)) {
          console.warn(`[skip] ${guide.role}/${step.id}: URL contains placeholder (${resolvedUrl})`);
          continue;
        }

        if (!loggedIn && step.url !== credentials[guide.role]?.loginPath) {
          console.warn(`[skip] ${guide.role}/${step.id}: login unavailable or failed`);
          continue;
        }

        await captureStep(page, guide.role, step, resolvedUrl);
      }
    }

    await page.close();
  }
} finally {
  await browser.close();
}

async function loginForRole(page, role) {
  const roleCredentials = credentials[role];

  if (!roleCredentials) {
    console.warn(`[warn] ${role}: no credentials configured`);
    return false;
  }

  const loginUrl = absoluteUrl(roleCredentials.loginPath);
  await gotoPage(page, loginUrl);
  await maskSensitiveUi(page);

  if (role === 'siswa') {
    if (!roleCredentials.idyayasan || !roleCredentials.token) {
      console.warn('[warn] siswa: GUIDE_SISWA_IDYAYASAN and GUIDE_SISWA_TOKEN are required for logged-in screenshots');
      return false;
    }

    await page.type('input[name="idyayasan"]', roleCredentials.idyayasan);
    await page.type('input[name="token"]', roleCredentials.token);
  } else {
    if (!roleCredentials.email || !roleCredentials.password) {
      console.warn(`[warn] ${role}: email/password credentials are incomplete`);
      return false;
    }

    await page.type('input[name="email"]', roleCredentials.email);
    await page.type('input[name="password"]', roleCredentials.password);
  }

  await Promise.all([
    page.click('button[type="submit"]'),
    page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: Number(process.env.GUIDE_TIMEOUT_MS || 20000) }).catch(() => null),
  ]);

  const currentUrl = page.url();
  const stillOnLogin = currentUrl.includes('/login');

  if (stillOnLogin) {
    console.warn(`[warn] ${role}: login appears to have failed (${currentUrl})`);
    return false;
  }

  return true;
}

async function captureStep(page, role, step, resolvedUrl) {
  const targetUrl = absoluteUrl(resolvedUrl);
  const outputPath = path.join(outputRoot, step.screenshot_path);

  await fs.mkdir(path.dirname(outputPath), { recursive: true });
  await gotoPage(page, targetUrl);
  await maskSensitiveUi(page);
  await dismissGuideOverlays(page, step);
  await page.screenshot({ path: outputPath, fullPage: true });
  console.log(`[ok] ${role}/${step.id} -> ${path.relative(rootDir, outputPath)}`);
}

async function gotoPage(page, url) {
  await page.goto(url, {
    waitUntil: 'domcontentloaded',
    timeout: Number(process.env.GUIDE_TIMEOUT_MS || 20000),
  });

  await new Promise((resolve) => setTimeout(resolve, Number(process.env.GUIDE_SETTLE_MS || 1200)));
}

async function maskSensitiveUi(page) {
  await page.addStyleTag({
    content: `
      input[type="password"],
      input[name="token"],
      input[name="idyayasan"],
      .guide-mask {
        filter: blur(4px) !important;
      }
    `,
  }).catch(() => null);
}

async function dismissGuideOverlays(page, step) {
  if (step.id === 'siswa-install-pwa') {
    return;
  }

  await page.evaluate(() => {
    const overlayTexts = [
      'Buka Ujian dari Aplikasi',
      'Install Aplikasi',
      'Browser tidak mendukung stay awake',
    ];

    for (const element of Array.from(document.querySelectorAll('body *'))) {
      const text = element.textContent || '';
      if (!overlayTexts.some((needle) => text.includes(needle))) {
        continue;
      }

      const fixedAncestor = element.closest('.fixed, [style*="position: fixed"]');
      if (fixedAncestor instanceof HTMLElement) {
        fixedAncestor.style.display = 'none';
        continue;
      }

      if (element instanceof HTMLElement) {
        element.style.display = 'none';
      }
    }
  }).catch(() => null);
}

function absoluteUrl(url) {
  if (/^https?:\/\//i.test(url)) {
    return url;
  }

  return `${baseUrl}${url.startsWith('/') ? url : `/${url}`}`;
}

function hasPlaceholder(url) {
  return /\{[^}]+\}/.test(url);
}

function validateGuideSource(source) {
  const requiredRoot = ['updated_at', 'app_version', 'guides'];
  for (const field of requiredRoot) {
    if (!(field in source)) {
      throw new Error(`Guide source is missing ${field}`);
    }
  }

  if (!Array.isArray(source.guides) || source.guides.length === 0) {
    throw new Error('Guide source must contain at least one guide');
  }

  for (const guide of source.guides) {
    for (const field of ['role', 'title', 'description', 'audience', 'sections']) {
      if (!guide[field]) {
        throw new Error(`Guide is missing ${field}`);
      }
    }

    for (const section of guide.sections) {
      if (!section.title || !Array.isArray(section.steps) || section.steps.length === 0) {
        throw new Error(`Guide ${guide.role} contains an invalid section`);
      }

      for (const step of section.steps) {
        for (const field of ['id', 'title', 'instruction', 'url', 'screenshot_path']) {
          if (!step[field]) {
            throw new Error(`Guide ${guide.role} contains a step missing ${field}`);
          }
        }
      }
    }
  }
}

function parseJsonEnv(name, fallback) {
  if (!process.env[name]) {
    return fallback;
  }

  try {
    return JSON.parse(process.env[name]);
  } catch (error) {
    throw new Error(`${name} must contain valid JSON: ${error.message}`);
  }
}

function deepMerge(base, override) {
  const result = { ...base };

  for (const [key, value] of Object.entries(override)) {
    result[key] = isPlainObject(value) && isPlainObject(result[key])
      ? deepMerge(result[key], value)
      : value;
  }

  return result;
}

function isPlainObject(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}
