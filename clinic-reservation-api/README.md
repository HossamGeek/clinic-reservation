# Clinic Reservation API - Laravel 12 Starter

ده Starter Project مبني علشان يشتغل على نفس Schema الموجودة في ملف SQL المرفوع.

## 1) تجهيز بيئة PHP / Laravel

### أسهل اختيار على ويندوز
استخدم **Laravel Herd** لأنه بينزل PHP و Composer و Laravel CLI مع بعض.

### خطوات التثبيت السريعة
1. نزّل وثبّت Herd على ويندوز.
2. افتح Terminal جديد بعد التثبيت.
3. اتأكد إن الأوامر دي شغالة:

```powershell
php -v
composer -V
laravel -V
```

### بديل رسمي من Laravel
ممكن تستخدم أمر التثبيت الرسمي من Laravel لتثبيت PHP و Composer و Laravel installer.

## 2) إنشاء مشروع Laravel جديد

```powershell
laravel new clinic-reservation-api
cd clinic-reservation-api
```

## 3) تثبيت الباكدجات المطلوبة

```powershell
composer require darkaonline/l5-swagger
```

> المشروع ده عامل Swagger باستخدام **PHP 8 Attributes**.

## 4) انسخ الملفات دي داخل مشروع Laravel الجديد
انسخ محتويات الفولدرات دي داخل مشروعك:
- `app/Models`
- `app/Http/Controllers/Api`
- `app/Http/Requests/Auth`
- `app/OpenApi`
- `database/migrations`
- `routes/api.php`

## 5) إعداد `.env`
مثال:

```env
APP_NAME="Clinic Reservation API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinic_reservation
DB_USERNAME=root
DB_PASSWORD=
```

## 6) شغّل المشروع

```powershell
php artisan key:generate
php artisan migrate
php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
php artisan serve
```

## 7) لينكات مهمة
- API base: `http://127.0.0.1:8000/api`
- Register endpoint: `POST /api/register`
- Swagger UI غالبًا: `http://127.0.0.1:8000/api/documentation`

## 8) Request مثال للـ register
### Patient
```json
{
  "full_name": "Ahmed Test",
  "email": "ahmed.test@example.com",
  "phone": "01012345678",
  "password": "secret123",
  "password_confirmation": "secret123",
  "role": "PATIENT",
  "gender": "MALE",
  "date_of_birth": "1999-01-15",
  "address": "Cairo",
  "medical_history": "No chronic diseases"
}
```

### Doctor
```json
{
  "full_name": "Dr. Mona Ali",
  "email": "mona.doctor@example.com",
  "phone": "01112345678",
  "password": "secret123",
  "password_confirmation": "secret123",
  "role": "DOCTOR",
  "specialty": "Dermatology",
  "available_time": "5PM - 9PM",
  "bio": "Senior dermatologist"
}
```

## 9) Response مثال
```json
{
  "message": "User registered successfully",
  "data": {
    "user": {
      "user_id": 10,
      "full_name": "Ahmed Test",
      "email": "ahmed.test@example.com",
      "phone": "01012345678",
      "role": "PATIENT",
      "created_at": "2026-04-18T01:00:00.000000Z"
    },
    "profile": {
      "patient_id": 5,
      "user_id": 10,
      "gender": "MALE",
      "date_of_birth": "1999-01-15",
      "address": "Cairo",
      "medical_history": "No chronic diseases",
      "created_at": "2026-04-18T01:00:00.000000Z"
    }
  }
}
```

## 10) ملاحظات مهمة
- جدول `users` في الـ dump بيستخدم `password_hash` بدل `password`، وده متظبط في الـ Model والـ Controller.
- المفاتيح الأساسية مخصصة: `user_id`, `patient_id`, `doctor_id`, `reservation_id`.
- العلاقات معمولة بـ Eloquent ORM ومطابقة للـ SQL dump.
- لو عايز بعد كده أضيف Login / Sanctum / Reservation CRUD هيبقى سهل فوق نفس الهيكل.
