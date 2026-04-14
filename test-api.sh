#!/bin/bash

echo "=== Test Login (Pending User - Should Fail) ==="
curl -s -X POST http://siwride.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@djali.com",
    "password": "password123"
  }' | jq .

echo ""
echo "=== Approve User via DB ==="
php artisan tinker --execute 'App\Models\Mobile\User::where("email", "admin@djali.com")->update(["status" => "approved"]);'

echo ""
echo "=== Test Login (Approved User - Should Success) ==="
curl -s -X POST http://siwride.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@djali.com",
    "password": "password123"
  }' | jq .
