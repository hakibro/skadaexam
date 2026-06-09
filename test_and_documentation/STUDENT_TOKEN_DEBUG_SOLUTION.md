🔧 **STUDENT LOGIN TOKEN ISSUE - DIAGNOSIS & SOLUTION**

## 🚨 **Problem**

Students are getting the error: "Token tidak valid atau sudah tidak aktif. Silahkan hubungi pengawas." even when the token should be valid.

## 🔍 **Root Cause Analysis**

The issue is likely one of these common problems:

### **1. No Valid EnrollmentUjian Records**

-   The system looks for `EnrollmentUjian` records with:
    -   Matching `siswa_id`
    -   Matching `token_login`
    -   Status `enrolled` or `active`
-   If any of these don't match, the login fails

### **2. Token Format Issues**

-   Tokens must be exactly **6 characters**
-   Tokens are converted to **UPPERCASE** automatically
-   Case sensitivity in database comparison

### **3. Student Data Issues**

-   Student must have `idyayasan` field populated
-   Payment status must be `'Lunas'`
-   Student record must exist

## ✅ **Implemented Solutions**

### **1. Enhanced Debugging in Login Controller**

```php
// Added comprehensive logging in SiswaLoginController
Log::info('Student login attempt', [
    'siswa_id' => $siswa->id,
    'idyayasan' => $idyayasan,
    'token_input' => $token,
    'payment_status' => $siswa->status_pembayaran,
]);

// Enhanced enrollment search with debugging
if (!$enrollment) {
    // Check what enrollments exist for this student
    $allEnrollments = EnrollmentUjian::where('siswa_id', $siswa->id)->get();
    $tokenEnrollments = EnrollmentUjian::where('token_login', $token)->get();

    Log::warning('No matching enrollment found', [
        'siswa_id' => $siswa->id,
        'token_search' => $token,
        'student_enrollments' => $allEnrollments->map(...),
        'token_matches' => $tokenEnrollments->map(...),
    ]);
}
```

### **2. Improved Token Validation**

```php
// Enhanced validateToken method in EnrollmentUjian model
public function validateToken($token)
{
    if ($this->token_login !== $token) {
        Log::debug('Token validation failed: mismatch', [
            'enrollment_id' => $this->id,
            'stored_token' => $this->token_login,
            'provided_token' => $token,
        ]);
        return false;
    }

    // Check if token is expired (used > 2 hours ago)
    if ($this->token_digunakan_pada && $this->token_digunakan_pada->addHours(2) < now()) {
        Log::debug('Token validation failed: expired', [
            'enrollment_id' => $this->id,
            'token_used_at' => $this->token_digunakan_pada,
        ]);
        return false;
    }

    return true;
}
```

### **3. More Flexible Enrollment Search**

```php
// First try exact match with active status
$enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
    ->where('token_login', $token)
    ->whereIn('status_enrollment', ['enrolled', 'active'])
    ->first();

// If not found, check for any enrollment with this token
if (!$enrollment) {
    $enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
        ->where('token_login', $token)
        ->first();

    if ($enrollment && !in_array($enrollment->status_enrollment, ['enrolled', 'active'])) {
        // Give specific error about enrollment status
        return back()->withErrors([
            'token' => 'Token ditemukan tapi status enrollment tidak aktif: ' .
                       $enrollment->status_enrollment
        ]);
    }
}
```

### **4. Debug Routes for Testing**

Created debug routes accessible at:

-   `http://skadaexam.test/debug/student-login` - View all enrollment data
-   `http://skadaexam.test/debug/create-student-test-data` - Create test data

## 🧪 **Testing Tools**

### **Test Data Creation**

Visit: `http://skadaexam.test/debug/create-student-test-data`

This creates:

-   Student with ID Yayasan: `TEST001`
-   Active session: `Test Session for Login`
-   Enrollment with Token: `TEST01`
-   Payment status: `Lunas`

### **Debugging Information**

Visit: `http://skadaexam.test/debug/student-login`

Shows:

-   All students with idyayasan
-   All enrollments with tokens
-   Step-by-step validation process
-   Current enrollment status

## 🔧 **How to Fix Token Issues**

### **Step 1: Verify Database Data**

```sql
-- Check if student exists with idyayasan
SELECT id, idyayasan, nama, status_pembayaran
FROM siswa
WHERE idyayasan = 'YOUR_ID_YAYASAN';

-- Check if enrollment exists with token
SELECT e.*, s.nama, sr.nama_sesi
FROM enrollment_ujian e
LEFT JOIN siswa s ON e.siswa_id = s.id
LEFT JOIN sesi_ruangan sr ON e.sesi_ruangan_id = sr.id
WHERE e.token_login = 'YOUR_TOKEN';
```

### **Step 2: Create Missing Data**

```sql
-- Create enrollment if missing
INSERT INTO enrollment_ujian (
    siswa_id,
    sesi_ruangan_id,
    status_enrollment,
    token_login,
    token_dibuat_pada
) VALUES (
    1,              -- Replace with actual siswa_id
    1,              -- Replace with actual sesi_ruangan_id
    'enrolled',
    'ABC123',       -- Your 6-character token
    NOW()
);
```

### **Step 3: Update Session Status**

```sql
-- Ensure session is active
UPDATE sesi_ruangan
SET status = 'berlangsung'
WHERE id = YOUR_SESI_ID;
```

## 🎯 **Common Issues & Solutions**

### **Issue 1: "Token tidak valid"**

**Cause:** No EnrollmentUjian record with matching siswa_id + token_login
**Solution:** Create enrollment record or verify token matches exactly

### **Issue 2: "Status enrollment tidak aktif"**

**Cause:** Enrollment exists but status is not 'enrolled' or 'active'  
**Solution:** Update enrollment status or create new enrollment

### **Issue 3: "Status pembayaran belum lunas"**

**Cause:** Student payment status is not 'Lunas'
**Solution:** Update student payment status

### **Issue 4: "Sesi ujian belum dimulai"**

**Cause:** Session status is not 'berlangsung' or 'belum_mulai'
**Solution:** Update session status and timing

## 📊 **Current System Status**

### **✅ Working Components:**

-   Student authentication with siswa guard
-   Token validation logic with debugging
-   Payment status validation
-   Session time validation
-   Comprehensive error messages
-   Debug tools and logging

### **🔧 Required for Testing:**

1. Student records with `idyayasan` populated
2. EnrollmentUjian records with valid `token_login`
3. Active SesiRuangan records
4. Proper status fields set correctly

## 🚀 **Quick Test Steps**

1. **Visit:** `http://skadaexam.test/debug/create-student-test-data`
2. **Copy credentials:** ID Yayasan: `TEST001`, Token: `TEST01`
3. **Go to:** `http://skadaexam.test/login/siswa`
4. **Login with:** ID Yayasan: `TEST001`, Token: `TEST01`
5. **Check logs:** `storage/logs/laravel.log` for debugging info

## 📝 **Log Analysis**

Check `storage/logs/laravel.log` for:

-   `Student login attempt` - Shows input data
-   `No matching enrollment found` - Shows search results
-   `Token validation failed` - Shows validation details

## 🎉 **Expected Result**

After implementing these fixes, the student login should:

1. ✅ Accept ID Yayasan + Token correctly
2. ✅ Validate payment status
3. ✅ Find matching enrollment
4. ✅ Validate token correctly
5. ✅ Check session timing
6. ✅ Login successfully and redirect to dashboard

The system now has comprehensive debugging and should clearly show exactly where the token validation is failing!
