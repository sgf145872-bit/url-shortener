# URL Shortener with Firebase and Vercel
موقع بسيط لاختصار الروابط باستخدام Node.js، Express، Firebase Realtime Database، وVercel.

## التثبيت
1. قم بتثبيت Node.js.
2. استنسخ المستودع: `git clone <رابط المستودع>`
3. انتقل إلى المجلد: `cd url-shortener`
4. قم بتثبيت الحزم: `npm install`
5. أضف متغيرات بيئية:
   - `FIREBASE_SERVICE_ACCOUNT`: محتوى ملف JSON لتفويض Firebase.
   - `FIREBASE_DATABASE_URL`: رابط قاعدة البيانات من Firebase.
6. شغل الخادم محليًا: `npm start`

## النشر على Vercel
1. قم بإنشاء مستودع على GitHub.
2. ارفع المشروع إلى GitHub.
3. أنشئ حساب على Vercel وقم بربط المستودع.
4. أضف متغيرات بيئية في إعدادات Vercel:
   - `FIREBASE_SERVICE_ACCOUNT`: محتوى ملف JSON.
   - `FIREBASE_DATABASE_URL`: رابط قاعدة البيانات.
5. انشر التطبيق.

## الاستخدام
- أدخل رابطًا طويلًا في الصفحة الرئيسية.
- انقر على "اختصر الرابط" للحصول على رابط قصير.
- استخدم الرابط القصير لإعادة التوجيه إلى الرابط الأصلي.
