document.getElementById('shortenForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const longUrl = document.getElementById('longUrl').value;
    const resultDiv = document.getElementById('result');

    try {
        const response = await fetch('/api/shorten', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `longUrl=${encodeURIComponent(longUrl)}`
        });
        const data = await response.json();
        if (data.shortUrl) {
            resultDiv.innerHTML = `<div class="alert alert-success">الرابط القصير: <a href="${data.shortUrl}" target="_blank">${data.shortUrl}</a></div>`;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
        }
    } catch (err) {
        resultDiv.innerHTML = `<div class="alert alert-danger">حدث خطأ، حاول مرة أخرى</div>`;
    }
});
