<?php echo "<?xml version='1.0'?>" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
<title>EMAIL TO REALMAIL</title>
<meta http-equiv='Content-type' content='text/html; charset=UTF-8'></meta>
</head>
<body>
<p>
<?php

$_POST['from'] = $_POST['signature'] . "\n" . $_POST['from'];

$_POST['from'] = str_replace("\r\n", '\\\\\\\\', $_POST['from']);
$_POST['from'] = str_replace("\n", '\\\\\\\\', $_POST['from']);
$_POST['from'] = str_replace('#', '\#', $_POST['from']);

$_POST['to'] = str_replace("\r\n", '\\\\\\\\', $_POST['to']);
$_POST['to'] = str_replace("\n", '\\\\\\\\', $_POST['to']);
$_POST['to'] = str_replace('#', '\#', $_POST['to']);

$_POST['body'] = str_replace('#', '\#', $_POST['body']);

$letter = '\documentclass{letter}'
        . '\signature{' . $_POST['signature'] . '}'
        . '\address{' . $_POST['from'] . '}'
        . '\begin{document}'
        . '\begin{letter}{' . $_POST['to'] . '}'
        . '\opening{' . $_POST['opening'] . '}'
        . "\n\n" . $_POST['body'] . "\n\n"
        . '\closing{' . $_POST['closing'] . '}'
        . '\end{letter}'
        . '\end{document}';

$tempfile = tempnam('texery', 'letter');

@system("echo \"$letter\" > $tempfile");
@system("pdflatex -output-directory texery $tempfile");

$to = $_POST['email'];
$subject = "Message!";
$body = "FROM:\n\n$_POST[from]\n\n"
      . "TO:\n\n$_POST[to]\n\n"
      . "BODY:\n\n$_POST[body]";

$boundary = md5(date('r', time())); 

$eol = "\r\n";

$headers = "Content-Type: multipart/mixed; boundary=$boundary" . $eol; 

$attachment = @chunk_split(base64_encode(file_get_contents($tempfile . '.pdf'))); 

$message = '--' . $boundary . $eol
         . 'Content-Type: text/plain; charset=ISO-8859-1' . $eol
         . $eol
         . $body . $eol
         . '--' . $boundary . $eol
         . 'Content-Type: application/pdf; name="letter.pdf"' . $eol
         . 'Content-Disposition: attachment; filename="letter.pdf"' . $eol
         . 'Content-Transfer-Encoding: base64' . $eol
         . $eol
         . $attachment . $eol
         . '--' . $boundary . '--';

$mail_sent = @mail($to, $subject, $message, $headers); 

echo '<br />';
echo '<br />';

if ($mail_sent) {
      echo("Message successfully sent to $to!");
} else {
      echo("Message delivery failed...");
}


?>
</p>
</body>
</html>
