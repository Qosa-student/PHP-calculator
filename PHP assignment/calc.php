<?php
$result = '';
$error = '';
$resultImage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num1 = trim($_POST['num1'] ?? '');
    $op = trim($_POST['operator'] ?? '');
    $num2 = trim($_POST['num2'] ?? '');

    if (!is_numeric($num1) || !is_numeric($num2)) {
        $error = 'Please enter two valid numbers.';
    } elseif (!in_array($op, ['+', '-', '*', '/'], true)) {
        $error = 'Please enter +, -, * or / as the operator.';
    } elseif ($op === '/' && (float) $num2 === 0.0) {
        $error = 'Cannot divide by zero.';
    } else {
        $firstNumber = (float) $num1;
        $secondNumber = (float) $num2;
        
        switch ($op) {
            case '+':
                $result = $firstNumber + $secondNumber;
                break;
            case '-':
                $result = $firstNumber - $secondNumber;
                break;
            case '*':
                $result = $firstNumber * $secondNumber;
                break;
            case '/':
                $result = $firstNumber / $secondNumber;
                break;
        }
    }
}

if ($error !== '') {
    $resultImage = 'images/error.png';
} elseif ($result !== '') {
    if ($result > 0) {
        $resultImage = 'images/positive.png';
    } elseif ($result < 0) {
        $resultImage = 'images/negative.png';
    } else {
        $resultImage = 'images/zero.png';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <title>Simple Calculator</title>
    </head>
    <body>
        <form method="POST" action="index.php">
        <h1 class="title">Calculator in PHP</h1>
        <p>Operators: <b>+</b> <b>-</b> <b>*</b> <b>/</b></p>
        <input type="text" name="num1" class="send-box" placeholder="First Number" required>
        <input type="text" name="operator" class="send-box" placeholder="Operator" maxlength="1" required>
        <input type="text" name="num2" class="send-box" placeholder="Second Number" required><br>
        <input type="submit" value="Calculate" class="btn" name="submit">
        </form>

        <?php if ($error !== ''): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <input type="text" class="send-box result" value="<?= htmlspecialchars((string) $result, ENT_QUOTES, 'UTF-8') ?>" placeholder="Result" readonly>

        <?php if ($resultImage !== ''): ?>
        <p><img src="<?= htmlspecialchars($resultImage, ENT_QUOTES, 'UTF-8') ?>" alt="Calculator result"></p>
        <?php endif; ?>
    </body>
</html>
