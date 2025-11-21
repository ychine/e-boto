<?php

test('registration screen is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('posting to register endpoint is disabled', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});
