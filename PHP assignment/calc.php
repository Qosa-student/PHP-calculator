<?php
// These variables store the answer and any error message to show in the page.
$result = '';
$error = '';
$resultImage = '';

// Run the calculator only after the form has been submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the values sent by the form and remove extra spaces.
    $num1 = trim($_POST['num1'] ?? '');
    $op = trim($_POST['operator'] ?? '');
    $num2 = trim($_POST['num2'] ?? '');

    // Check that both number inputs contain valid numbers.
    if (!is_numeric($num1) || !is_numeric($num2)) {
        $error = 'Please enter two valid numbers.';
    // Check that the operator is one of the supported operators.
    } elseif (!in_array($op, ['+', '-', '*', '/'], true)) {
        $error = 'Please enter +, -, * or / as the operator.';
    // Division by zero is not a valid calculation.
    } elseif ($op === '/' && (float) $num2 === 0.0) {
        $error = 'Cannot divide by zero.';
    } else {
        // Convert the text input into decimal numbers for the calculation.
        $firstNumber = (float) $num1;
        $secondNumber = (float) $num2;

        // Choose the calculation that matches the operator entered by the user.
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

// Choose an image based on what result.
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
        <!-- These inputs send the first number, operator, and second number to PHP. -->
        <input type="text" name="num1" class="send-box" placeholder="First Number" required>
        <input type="text" name="operator" class="send-box" placeholder="Operator" maxlength="1" required>
        <input type="text" name="num2" class="send-box" placeholder="Second Number" required><br>
        <input type="submit" value="Calculate" class="btn" name="submit">
        </form>

        <!-- Show an error only when validation found a problem. -->
        <?php if ($error !== ''): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <!-- Display the answer calculated by PHP. -->
        <input type="text" class="send-box result" value="<?= htmlspecialchars((string) $result, ENT_QUOTES, 'UTF-8') ?>" placeholder="Result" readonly>

        <?php if ($resultImage !== ''): ?>
        <!-- Display the image that matches the result. -->
        <p><img src="<?= htmlspecialchars($resultImage, ENT_QUOTES, 'UTF-8') ?>" alt="Calculator result"></p>
        <?php endif; ?>
    </body>
</html>