<?php
$expression = $_POST['expression'] ?? ''; // Gets the calculation typed by the user.
$result = null; // Stores the calculation answer.
$error = ''; // Stores an error message, if one occurs.
$message = ''; // Stores the positive, negative, or zero message.
$pageClass = ''; // Chooses the background image class.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($expression)) {
        $error = 'Please enter a calculation expression.';
        $pageClass = 'error';

    } elseif (strlen($expression) > 50 || !preg_match('/^[0-9+\-*\/().%\s]+$/', $expression)) {
        // Only digits, spaces, and the operators + - * / % ( ) are allowed or else its error.
        $error = 'Use only numbers and basic operators.';
        $pageClass = 'error';

    } else {
        try {
            // Turn "%" into "/100" so 50% becomes 50/100.
            $expression = str_replace('%', '/100', $expression);

            // Safely calculate the expression.
            $result = calculateExpression($expression);

            // Make sure we ended up with a normal, finite number.
            if (!is_finite($result)) {
                throw new Exception('That calculation is not valid.');
            }
            // Round to 8 decimal places and strip trailing zeros (e.g. 2.50000000 -> 2.5).
            $result = rtrim(rtrim(number_format($result, 8, '.', ''), '0'), '.');
            if ($result === '' || $result === '-') {
                $result = '0';
            }
            // Pick a message and background image based on the answer.
            if ($result > 0) {
                $message = 'Positive result.';
                $pageClass = 'positive';
            } elseif ($result < 0) {
                $message = 'Negative result.';
                $pageClass = 'negative';
            } else {
                $message = 'The result is zero.';
                $pageClass = 'zero';
            }

        } catch (Throwable $exception) {
            // Anything that goes wrong (bad syntax, divide by zero, etc.)
            // ends up here with one simple message for the user.
            $error = 'Enter a valid calculation expression.';
            $pageClass = 'error';
        }
    }
}

function calculateExpression(string $expression): float
{
    // Break the expression into a flat list of tokens (pieces),
    // e.g. "2+3*4" becomes ["2", "+", "3", "*", "4"].
    preg_match_all('/\d+\.?\d*|\.\d+|[+\-*\/()]/', $expression, $matches);
    $tokens = $matches[0];
    $position = 0; // Tracks which token we're currently looking at.

    // Reads the next number token and moves forward.
    $readNumber = function () use (&$tokens, &$position) {
        if (!isset($tokens[$position]) || !is_numeric($tokens[$position])) {
            throw new Exception('Expected a number.');
        }
        return (float) $tokens[$position++];
    };

    // Handles the innermost pieces: plain numbers, parentheses,
    // and a leading minus sign like "-5".
    $parseFactor = function () use (&$tokens, &$position, &$parseExpression, &$parseFactor, $readNumber) {
        $token = $tokens[$position] ?? null;

        if ($token === '-') {
            $position++;
            return -$parseFactor();
        }
        if ($token === '(') {
            $position++; // skip "("
            $value = $parseExpression();
            if (($tokens[$position] ?? null) !== ')') {
                throw new Exception('Missing closing parenthesis.');
            }
            $position++; // skip ")"
            return $value;
        }
        return $readNumber();
    };

    // Handles * / % between factors, e.g. "3 * 4 / 2".
    $parseTerm = function () use (&$tokens, &$position, $parseFactor) {
        $value = $parseFactor();
        while (in_array($tokens[$position] ?? null, ['*', '/'], true)) {
            $operator = $tokens[$position++];
            $next = $parseFactor();
            if ($operator === '*') {
                $value *= $next;
            } else {
                if ($next == 0) {
                    throw new Exception('Cannot divide by zero.');
                }
                $value /= $next;
            }
        }
        return $value;
    };

    // Handles + and - between terms, e.g. "3 + 4 - 2".
    $parseExpression = function () use (&$tokens, &$position, $parseTerm) {
        $value = $parseTerm();
        while (in_array($tokens[$position] ?? null, ['+', '-'], true)) {
            $operator = $tokens[$position++];
            $next = $parseTerm();
            $value = ($operator === '+') ? $value + $next : $value - $next;
        }
        return $value;
    };

    $value = $parseExpression();

    // If any tokens are left over, the expression had extra/bad symbols.
    if ($position !== count($tokens)) {
        throw new Exception('Unexpected symbol in expression.');
    }

    return $value;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PHP Calculator</title>
        <style>
            * {
                box-sizing: border-box;
            } body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 20px;
                background: #f0f0f0;
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                color: #203239;
                font-family: Arial, sans-serif;
            } body.positive { background-image: url('images/positive.png');
            } body.negative { background-image: url('images/negative.png'); 
            } body.zero { background-image: url('images/zero.png');
            } body.error { background-image: url('images/error.png');
            } main {
                width: min(100%, 360px);
                padding: 28px;
                background: #fff;
                border-radius: 14px;
                box-shadow: 0 12px 30px #9fb3b866;
            } h1 {
                margin: 0 0 6px;
                font-size: 1.6rem;
            } .hint {
                margin: 0 0 20px;
                color: #60747a;
                font-size: .9rem;
            } input {
                width: 100%;
                padding: 14px;
                border: 2px solid #c8d7da;
                border-radius: 8px;
                font-size: 1.2rem;
            } button {
                width: 100%;
                margin: 12px 0 0;
                padding: 13px;
                border: 0;
                border-radius: 8px;
                background: #247b78;
                color: #fff;
                font-size: 1rem;
                cursor: pointer;
            } button:hover {
                background: #1b625f;
            } .result {
                margin-top: 20px;
                padding: 18px;
            } .answer {
                margin: 6px 0;
                font-size: 1.8rem;
                font-weight: bold;
            } .error {
                margin-top: 16px;
                color: #b42318;
            }
        </style>
    </head>
    <body class="<?= htmlspecialchars($pageClass) ?>">
        <main>
            <h1>PHP Calculator</h1>
            <p class="hint">Try: Number + Number, Number - Number, Number * Number, Number / Number, or just simple stuff</p>

            <form method="POST">
                <input name="expression" value="<?= htmlspecialchars($expression) ?>" placeholder="Enter a calculation" autofocus>
                <button type="submit">Calculate</button>
            </form>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php elseif ($result !== null): ?>
                <section class="result" aria-live="polite">
                    <div class="answer"><?= htmlspecialchars($result) ?></div>
                    <div><?= htmlspecialchars($message) ?></div>
                </section>
            <?php endif; ?>
        </main>
    </body>
</html>