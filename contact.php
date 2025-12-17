<?php
// ==== 設定（変更してね）====
$TO   = "iijima@itto110.com";           // 受信先（あなたのメール）
$FROM = "info@itto110.com";         // 差出人（サイトのドメイン推奨）
$SUBJECT = "【一陶HP】お問い合わせが届きました";
// ==========================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

// ハニーポット → 入力があればスパムとして無視
if (!empty($_POST['website'])) {
  http_response_code(200);
  exit;
}

// 入力取得
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$tel     = trim($_POST['tel'] ?? '');
$message = trim($_POST['message'] ?? '');

// バリデーション
$errors = [];
if ($name === '')   $errors[] = 'お名前は必須です。';
if ($message === '')$errors[] = 'お問い合わせ内容は必須です。';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'メールアドレスの形式が正しくありません。';

if ($errors) {
  // 簡易表示（必要ならエラーページへ）
  echo "送信できませんでした：<br><ul>";
  foreach ($errors as $e) echo "<li>".htmlspecialchars($e, ENT_QUOTES, 'UTF-8')."</li>";
  echo "</ul>";
  exit;
}

// 日本語メール設定
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// 本文（運営宛）
$body = "一陶HPの問い合わせフォームから送信がありました。\n\n"
      . "■お名前：{$name}\n"
      . "■メール：{$email}\n"
      . "■電話番号：{$tel}\n"
      . "■内容：\n{$message}\n\n"
      . "----\n送信日時：" . date('Y-m-d H:i:s');

// ヘッダ
$headers  = "From: " . $FROM . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";

// 送信
$ok = mb_send_mail($TO, $SUBJECT, $body, $headers);

// 自動返信（ユーザー宛）
if ($ok) {
  $auto_subject = "【自動返信】お問い合わせありがとうございます";
  $auto_body = "{$name} 様\n\nこの度はお問い合わせありがとうございます。\n以下の内容で受け付けました。\n\n"
             . "----- コピー -----\n{$body}\n--------------\n\n株式会社 一陶";
  $auto_headers  = "From: " . $FROM . "\r\n";
  mb_send_mail($email, $auto_subject, $auto_body, $auto_headers);
}

// 完了
if ($ok) {
  header("Location: /thanks.html");
  exit;
} else {
  echo "送信に失敗しました。時間をおいて再度お試しください。";
}