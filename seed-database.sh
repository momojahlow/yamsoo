#!/bin/bash

echo "🌱 Seeding Yamsoo Database..."

# Vider la base de données
echo "🗑️  Clearing database..."
php artisan migrate:fresh

# Exécuter les seeders
echo "📦 Running seeders..."
php artisan db:seed

echo "✅ Database seeded successfully!"
echo ""
echo "📋 Available test accounts:"
echo "   👤 Test User: test@example.com / password"
echo ""
echo "👨‍👩‍👧‍👦 Family accounts:"
echo "   👨 Ahmed Benali: ahmed.benali@example.com / password"
echo "   👩 Fatima Zahra: fatima.zahra@example.com / password"
echo "   👧 Amina Tazi: amina.tazi@example.com / password"
echo ""
echo "💑 Couples:"
echo "   👨 Mohammed Alami: mohammed.alami@example.com / password"
echo "   👩 Leila Mansouri: leila.mansouri@example.com / password"
echo "   👨 Youssef Bennani: youssef.bennani@example.com / password"
echo "   👩 Sara Benjelloun: sara.benjelloun@example.com / password"
echo "   👨 Hassan Idrissi: hassan.idrissi@example.com / password"
echo "   👩 Hanae Mernissi: hanae.mernissi@example.com / password"
echo ""
echo "👥 Other users:"
echo "   👨 Karim El Fassi: karim.elfassi@example.com / password"
echo "   👨 Omar Cherkaoui: omar.cherkaoui@example.com / password"
echo "   👩 Nadia Berrada: nadia.berrada@example.com / password"
echo "   👩 Zineb El Khayat: zineb.elkhayat@example.com / password"
echo "   👨 Adil Benslimane: adil.benslimane@example.com / password"
echo "   👨 Rachid Alaoui: rachid.alaoui@example.com / password"
echo ""
echo "🚀 You can now test the application with these accounts!"
