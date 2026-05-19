<?php
date_default_timezone_set('Europe/Moscow');

$tasks = array(
    'area_triangle' => 'Площадь треугольника',
    'perimeter_triangle' => 'Периметр треугольника',
    'volume_box' => 'Объем параллелепипеда',
    'mean' => 'Среднее арифметическое',
    'max_value' => 'Максимум из A, B и C',
    'sum_squares' => 'Сумма квадратов A, B и C'
);

function makeNumber()
{
    if (mt_rand(0, 1) == 1) {
        return (string) mt_rand(0, 100);
    } else {
        return number_format(mt_rand(0, 10000) / 100, 2, '.', '');
    }
}

function getNumber($value)
{
    $value = str_replace(',', '.', trim($value));

    if ($value === '' || !is_numeric($value)) {
        return false;
    }

    return (float) $value;
}

function outValue($value)
{
    if ((int) $value == $value) {
        return (string) ((int) $value);
    } else {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}

$fio = '';
$group = '';
$about = '';
$a_val = makeNumber();
$b_val = makeNumber();
$c_val = makeNumber();
$task = 'mean';
$human_result = '';
$mail = '';
$view = 'browser';
$send_mail = false;

if (isset($_GET['fio'])) {
    $fio = $_GET['fio'];
}

if (isset($_GET['group'])) {
    $group = $_GET['group'];
}

if (isset($_POST['A'])) {
    $fio = trim($_POST['FIO']);
    $group = trim($_POST['GROUP']);
    $about = trim($_POST['ABOUT']);
    $a_val = trim($_POST['A']);
    $b_val = trim($_POST['B']);
    $c_val = trim($_POST['C']);
    $task = $_POST['TASK'];
    $human_result = trim($_POST['result']);
    $mail = trim($_POST['MAIL']);
    $view = $_POST['VIEW'];
    $send_mail = array_key_exists('send_mail', $_POST);

    $a = getNumber($a_val);
    $b = getNumber($b_val);
    $c = getNumber($c_val);
    $human = getNumber($human_result);

    if ($a === false || $b === false || $c === false) {
        $result = 'error';
        $calc_text = 'Результат не вычислен из-за ошибки во входных данных.';
    } else {
        if ($task == 'area_triangle') {
            if ($a <= 0 || $b <= 0 || $c <= 0 || $a + $b <= $c || $a + $c <= $b || $b + $c <= $a) {
                $result = 'error';
                $calc_text = 'Площадь не может быть вычислена: значения не образуют треугольник.';
            } else {
                $p = ($a + $b + $c) / 2;
                $result = round(sqrt($p * ($p - $a) * ($p - $b) * ($p - $c)), 2);
                $calc_text = outValue($result);
            }
        } else if ($task == 'perimeter_triangle') {
            $result = round($a + $b + $c, 2);
            $calc_text = outValue($result);
        } else if ($task == 'volume_box') {
            $result = round($a * $b * $c, 2);
            $calc_text = outValue($result);
        } else if ($task == 'mean') {
            $result = round(($a + $b + $c) / 3, 2);
            $calc_text = outValue($result);
        } else if ($task == 'max_value') {
            $result = round(max($a, $b, $c), 2);
            $calc_text = outValue($result);
        } else if ($task == 'sum_squares') {
            $result = round($a * $a + $b * $b + $c * $c, 2);
            $calc_text = outValue($result);
        } else {
            $result = 'error';
            $calc_text = 'Выбран неизвестный тип задачи.';
        }
    }

    if ($human_result === '') {
        $expected_text = 'Задача самостоятельно решена не была.';
    } else if ($human === false) {
        $expected_text = 'Переданный результат не является числом.';
    } else {
        $expected_text = outValue($human);
    }

    if ($result !== 'error' && $human !== false && $human_result !== '' && abs($result - $human) < 0.01) {
        $status_text = 'Тест пройден';
    } else {
        $status_text = 'Ошибка: тест не пройден';
    }

    if ($about == '') {
        $about_text = 'Сведения о себе не указаны.';
    } else {
        $about_text = htmlspecialchars($about, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    $out_text = '';
    $out_text .= '<div><dt>ФИО</dt><dd>' . htmlspecialchars($fio, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</dd></div>';
    $out_text .= '<div><dt>Группа</dt><dd>' . htmlspecialchars($group, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</dd></div>';
    $out_text .= '<div><dt>Немного о себе</dt><dd>' . $about_text . '</dd></div>';
    $out_text .= '<div><dt>Тип задачи</dt><dd>' . htmlspecialchars($tasks[$task], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</dd></div>';

    if ($a === false || $b === false || $c === false) {
        $input_text = 'A, B и C должны быть числами.';
    } else {
        $input_text = 'A = ' . outValue($a) . ', B = ' . outValue($b) . ', C = ' . outValue($c);
    }

    $out_text .= '<div><dt>Входные данные</dt><dd>' . htmlspecialchars($input_text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</dd></div>';
    $out_text .= '<div><dt>Предполагаемый результат</dt><dd>' . htmlspecialchars($expected_text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</dd></div>';
    $out_text .= '<div><dt>Вычисленный программой результат</dt><dd>' . htmlspecialchars($calc_text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</dd></div>';
    $out_text .= '<div><dt>Вывод</dt><dd class="' . ($status_text == 'Тест пройден' ? 'ok' : 'fail') . '">' . htmlspecialchars($status_text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</dd></div>';

    if ($send_mail) {
        if ($mail != '') {
            $mail_text = 'ФИО: ' . $fio . "\r\n";
            $mail_text .= 'Группа: ' . $group . "\r\n";
            $mail_text .= 'Тип задачи: ' . $tasks[$task] . "\r\n";
            $mail_text .= 'Входные данные: ' . $input_text . "\r\n";
            $mail_text .= 'Предполагаемый результат: ' . $expected_text . "\r\n";
            $mail_text .= 'Вычисленный программой результат: ' . $calc_text . "\r\n";
            $mail_text .= 'Итог: ' . $status_text;

            $mail_ok = @mail($mail, 'Результат тестирования', $mail_text, "Content-Type: text/plain; charset=UTF-8\r\n");

            $mail_info = 'Результаты теста были автоматически отправлены на e-mail ' . htmlspecialchars($mail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '.';
            if (!$mail_ok) {
                $mail_info .= ' На текущем сервере функция mail() не подтвердила отправку.';
            }
        } else {
            $mail_info = 'E-mail не указан, поэтому автоматическая отправка результата не выполнена.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анашин Александр 241-352. ЛР6: тест математических знаний</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="page-header" style="display: flex; align-items: center; gap: 15px;">
		<img class="logo" src="mospolytech_logo.png" width="150px" text-align="center" alt="Логотип университета">
        <div>
			<h1>ЛР6: тест математических знаний</h1>
			<p>Анашин Александр 241-352. Форма, обработка POST и отчет на одной PHP-странице</p>
		</div>
    </header>

    <main class="page-main">
        <?php
        if (isset($out_text)) {
            echo '<section class="panel report ' . ($view == 'print' ? 'report-print' : 'report-browser') . '">';
            echo '<h2>Результаты теста</h2>';
            echo '<dl class="report-list">';
            echo $out_text;
            echo '</dl>';

            if (isset($mail_info)) {
                echo '<p class="mail-info">' . $mail_info . '</p>';
            }

            if ($view == 'browser') {
                echo '<a class="repeat-link" href="?fio=' . rawurlencode($fio) . '&group=' . rawurlencode($group) . '">Повторить тест</a>';
            }

            echo '</section>';
        } else {
            echo '<section class="panel">';
            echo '<h2>Форма тестирования</h2>';
            echo '<form method="post" action="" class="lab-form">';

            echo '<label class="form-row"><span>ФИО</span><input type="text" name="FIO" value="' . htmlspecialchars($fio, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" required></label>';
            echo '<label class="form-row"><span>Номер группы</span><input type="text" name="GROUP" value="' . htmlspecialchars($group, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" required></label>';
            echo '<label class="form-row"><span>Немного о себе</span><textarea name="ABOUT" rows="4">' . htmlspecialchars($about, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</textarea></label>';

            echo '<label class="form-row"><span>Тип задачи</span><select name="TASK">';
            foreach ($tasks as $key => $value) {
                echo '<option value="' . $key . '"';
                if ($task == $key) {
                    echo ' selected';
                }
                echo '>' . $value . '</option>';
            }
            echo '</select></label>';

            echo '<label class="form-row"><span>Значение A</span><input type="text" name="A" value="' . htmlspecialchars($a_val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" required></label>';
            echo '<label class="form-row"><span>Значение B</span><input type="text" name="B" value="' . htmlspecialchars($b_val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" required></label>';
            echo '<label class="form-row"><span>Значение C</span><input type="text" name="C" value="' . htmlspecialchars($c_val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" required></label>';
            echo '<label class="form-row"><span>Ваш ответ</span><input type="text" name="result" value="' . htmlspecialchars($human_result, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"></label>';

            echo '<label class="form-row"><span>Версия страницы</span><select name="VIEW">';
            echo '<option value="browser"';
            if ($view == 'browser') {
                echo ' selected';
            }
            echo '>Версия для просмотра в браузере</option>';
            echo '<option value="print"';
            if ($view == 'print') {
                echo ' selected';
            }
            echo '>Версия для печати</option>';
            echo '</select></label>';

            echo '<label class="check-row">';
            echo '<input type="checkbox" id="send_mail" name="send_mail" value="1"';
            if ($send_mail) {
                echo ' checked';
            }
            echo ' onclick="obj=document.getElementById(\'mailRow\'); if(this.checked) obj.style.display=\'grid\'; else obj.style.display=\'none\';">';
            echo '<span>Отправить результат теста по e-mail</span>';
            echo '</label>';

            echo '<label class="form-row" id="mailRow" style="display:' . ($send_mail ? 'grid' : 'none') . ';">';
            echo '<span>Ваш e-mail</span><input type="email" name="MAIL" value="' . htmlspecialchars($mail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
            echo '</label>';

            echo '<button type="submit" class="submit-button">Проверить</button>';
            echo '</form>';
            echo '</section>';
        }
        ?>
    </main>

    <footer class="page-footer">
        Сформировано <?php echo date('d.m.Y в H:i:s'); ?>
    </footer>
</body>
</html>
