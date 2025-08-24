const express = require('express');
const firebase = require('firebase-admin');
const shortid = require('shortid');
const app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// تهيئة Firebase
try {
    const serviceAccount = JSON.parse(process.env.FIREBASE_SERVICE_ACCOUNT);
    firebase.initializeApp({
        credential: firebase.credential.cert(serviceAccount),
        databaseURL: process.env.FIREBASE_DATABASE_URL
    });
    console.log('Firebase initialized successfully');
} catch (err) {
    console.error('Firebase initialization error:', err.message);
    throw err;
}

const db = firebase.database();

// إنشاء رابط قصير
app.post('/shorten', async (req, res) => {
    const { longUrl } = req.body;
    console.log('Received POST request with longUrl:', longUrl);
    if (!longUrl) {
        console.error('Missing longUrl in request');
        return res.status(400).json({ error: 'الرابط الطويل مطلوب' });
    }
    if (!longUrl.match(/^https?:\/\//)) {
        console.error('Invalid URL:', longUrl);
        return res.status(400).json({ error: 'الرابط غير صالح، يجب أن يبدأ بـ http:// أو https://' });
    }
    const shortCode = shortid.generate();

    try {
        console.log('Attempting to save URL:', longUrl, 'with code:', shortCode);
        await db.ref('links/' + shortCode).set({
            longUrl,
            createdAt: Date.now()
        });
        const shortUrl = `https://${req.headers.host}/${shortCode}`;
        console.log('Short URL created:', shortUrl);
        res.json({ shortUrl });
    } catch (err) {
        console.error('Error saving URL:', err.message);
        res.status(500).json({ error: 'خطأ في الخادم: ' + err.message });
    }
});

// إعادة توجيه الرابط القصير
app.get('/:shortCode', async (req, res) => {
    const { shortCode } = req.params;
    console.log('Received GET request for shortCode:', shortCode);
    try {
        const snapshot = await db.ref('links/' + shortCode).once('value');
        const url = snapshot.val();
        if (url && url.longUrl) {
            console.log('Redirecting to:', url.longUrl);
            return res.redirect(url.longUrl);
        } else {
            console.error('Short code not found:', shortCode);
            return res.status(404).json({ error: 'الرابط القصير غير موجود' });
        }
    } catch (err) {
        console.error('Error fetching URL:', err.message);
        res.status(500).json({ error: 'خطأ في الخادم: ' + err.message });
    }
});

module.exports = app;
