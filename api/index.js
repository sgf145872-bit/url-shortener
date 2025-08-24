const express = require('express');
const firebase = require('firebase-admin');
const shortid = require('shortid');
const app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// تهيئة Firebase
const serviceAccount = JSON.parse(process.env.FIREBASE_SERVICE_ACCOUNT || '{}');
firebase.initializeApp({
    credential: firebase.credential.cert(serviceAccount),
    databaseURL: process.env.FIREBASE_DATABASE_URL || 'https://your-project-id.firebaseio.com'
});
const db = firebase.database();

// إنشاء رابط قصير
app.post('/shorten', async (req, res) => {
    const { longUrl } = req.body;
    const shortCode = shortid.generate();

    try {
        await db.ref('links/' + shortCode).set({
            longUrl,
            createdAt: Date.now()
        });
        res.json({ shortUrl: `https://${req.headers.host}/${shortCode}` });
    } catch (err) {
        res.status(500).json({ error: 'خطأ في الخادم' });
    }
});

// إعادة توجيه الرابط القصير
app.get('/:shortCode', async (req, res) => {
    const { shortCode } = req.params;
    const snapshot = await db.ref('links/' + shortCode).once('value');
    const url = snapshot.val();
    if (url && url.longUrl) {
        return res.redirect(url.longUrl);
    } else {
        res.status(404).send('الرابط غير موجود');
    }
});

module.exports = app;
