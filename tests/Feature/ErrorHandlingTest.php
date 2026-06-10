<?php

it('shows a friendly 404 page for browser requests', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertStatus(404)
        ->assertSee('Halaman Tidak Ditemukan')
        ->assertSee('Kembali');
});

it('returns a consistent json response for missing api routes', function () {
    $this->getJson('/api/halaman-yang-tidak-ada')
        ->assertStatus(404)
        ->assertJson([
            'message' => 'Alamat yang diminta tidak ditemukan.',
            'status' => 404,
        ]);
});
