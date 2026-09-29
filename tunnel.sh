#!/bin/bash
# Jalanin server lokal + ngrok bareng, biar dapet link publik buat testing di HP
# (WA, QR, dll). Link-nya acak tiap dijalankan -- kalau mau link tetap, klaim
# domain statis gratis dulu di https://dashboard.ngrok.com/domains lalu jalankan:
#   ngrok http --domain=nama-domainmu.ngrok-free.app 8000

cd "$(dirname "$0")"

# Nyalain php artisan serve kalau port 8000 belum ada yang pakai
if ! curl -s -o /dev/null http://127.0.0.1:8000; then
    echo "Menyalakan php artisan serve..."
    nohup php artisan serve > /tmp/artisan-serve.log 2>&1 &
    sleep 2
fi

# Nyalain ngrok kalau belum jalan
if ! curl -s -o /dev/null http://127.0.0.1:4040/api/tunnels; then
    echo "Menyalakan ngrok..."
    nohup ngrok http 8000 --log=stdout > /tmp/ngrok.log 2>&1 &
    sleep 3
fi

echo ""
echo "Link publik kamu:"
curl -s http://127.0.0.1:4040/api/tunnels | grep -o '"public_url":"[^"]*"' | head -1 | cut -d'"' -f4
