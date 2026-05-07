<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2D Array Loop</title>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
        .section { margin-bottom: 30px; border-bottom: 1px solid #ddd; padding-bottom: 15px; }
        table { border-collapse: collapse; margin-top: 10px; }
        td { border: 1px solid #ccc; padding: 8px 15px; text-align: center; }
        .highlight { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>

    <?php
    $matrix = [
        [1, 45, 38],
        [1, 9, 50],
        [1, 0, 3]
    ];
    ?>

    <div class="section">
        <h3>1. Using For Loop (Filtering >= 10)</h3>
        <?php
        for ($i = 0; $i < count($matrix); $i++) {
            for ($j = 0; $j < count($matrix[$i]); $j++) {
                if ($matrix[$i][$j] >= 10) {
                    echo "Row: $i, Col: $j &rarr; <b>" . $matrix[$i][$j] . "</b><br>";
                }
            }
        }
        ?>
    </div>

    <div class="section">
        <h3>2. Using Foreach Loop (Table Format)</h3>
        <table>
            <?php foreach ($matrix as $i => $row): ?>
                <tr>
                    <?php foreach ($row as $j => $val): ?>
                        <td class="<?php echo ($val >= 10) ? 'highlight' : ''; ?>">
                            <?php echo $val; ?>
                            <br><small>(R:<?php echo $i; ?>, C:<?php echo $j; ?>)</small>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
        <p><small>* Red numbers are >= 10</small></p>
    </div>

</body>
</html>
