<?php

header('Content-Type: text/plain; charset=UTF-8');

// منع توقف السكربت حتى لو الملف كبير
set_time_limit(0);
ini_set('memory_limit', '-1');

// رابط API
$url = 'https://gmailver.com/php/check2.php';

// المفتاح
$apiKey = "iud77b99c1bb94f6f477e29a50fb3b403";

// ملفات الإخراج
$liveFile = "live.txt";
$badFile  = "Bad.txt";
$mainFile = "hhh.txt";

// تحميل الإيميلات من الملفات السابقة (لمنع التكرار)
$checkedLive = file_exists($liveFile) ? file($liveFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$checkedBad  = file_exists($badFile)  ? file($badFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

// نخزن الكل في مصفوفة وحده لسهولة التحقق
$alreadyChecked = array_map('strtolower', array_merge($checkedLive, $checkedBad));

// نقرأ الإيميلات من hhh.txt
$emails = file($mainFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (!$emails) {
    die("📂 الملف $mainFile فارغ أو غير موجود.\n");
}

// إزالة التكرارات + تنظيف
$emails = array_unique(array_map('trim', $emails));

// نزيل الإيميلات اللي تم فحصها مسبقاً
$newEmails = [];
foreach ($emails as $email) {
    if (!in_array(strtolower($email), $alreadyChecked)) {
        $newEmails[] = $email;
    } else {
        echo "⚠️ تم تجاهل $email (موجود مسبقاً في live.txt أو Bad.txt)\n";
    }
}

// تحديث ملف hhh.txt بحيث يحتوي فقط على الإيميلات الجديدة
file_put_contents($mainFile, implode(PHP_EOL, $newEmails) . PHP_EOL);

if (empty($newEmails)) {
    die("✅ لا يوجد إيميلات جديدة لفحصها.\n");
}

$total = count($newEmails);
$liveCount = 0;
$badCount = 0;

echo "🚀 بدء الفحص، العدد الكلي: $total ايميل\n\n";

// حلقة مستمرة حتى تنتهي جميع الإيميلات
while (true) {
    // تحديث القائمة من الملف (للتأكد إننا ما نعيد اللي خلص)
    $remainingEmails = file($mainFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$remainingEmails || count($remainingEmails) === 0) {
        break; // خلصنا كل شيء
    }

    // ناخذ أول 50 فقط
    $chunk = array_slice($remainingEmails, 0, 50);

    echo "🔎 فحص " . count($chunk) . " ايميل...\n";

    $data = [
        "mail" => $chunk,
        "key" => $apiKey,
        "fastCheck" => false
    ];

    $json = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=UTF-8',
        'Content-Length: ' . strlen($json)
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);

    if ($response === false) {
        echo "❌ خطأ cURL: " . curl_error($ch) . "\n";
        curl_close($ch);
        continue;
    }

    curl_close($ch);

    // معالجة الرد
    $result = json_decode($response, true);

    if (!$result || !isset($result['data'])) {
        echo "⚠️ رد غير متوقع من السيرفر:\n$response\n";
        continue;
    }

    foreach ($result['data'] as $item) {
        $email = $item['email'];
        $status = strtolower($item['status']); // live, bad, die...

        if ($status === "live") {
            file_put_contents($liveFile, $email . PHP_EOL, FILE_APPEND);
            $liveCount++;
            echo "✅ LIVE: $email\n";
        } else {
            file_put_contents($badFile, $email . PHP_EOL, FILE_APPEND);
            $badCount++;
            echo "❌ BAD: $email\n";
        }
    }

    // نحذف الإيميلات اللي اتفحصت من hhh.txt
    $remainingEmails = array_diff($remainingEmails, $chunk);
    file_put_contents($mainFile, implode(PHP_EOL, $remainingEmails) . (count($remainingEmails) ? PHP_EOL : ""));
}

echo "\n🎉 تم الفحص بالكامل!\n";
echo "📌 المجموع: $total ايميل\n";
echo "✅ LIVE: $liveCount\n";
echo "❌ BAD: $badCount\n";
echo "📂 المتبقي في $mainFile فارغ (تم فحص الكل)\n";