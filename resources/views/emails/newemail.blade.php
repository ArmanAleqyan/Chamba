<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обновление электронной почты</title>
    <style>
        /* Стили для контейнера */
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            font-family: Arial, sans-serif;
        }

        /* Стили для заголовка */
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Стили для текстового блока */
        p {
            line-height: 1.5;
        }

        /* Стили для кнопки */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Обновление электронной почты</h1>
    <p>Добрый день, <strong>{{ $details['name']  }}</strong>!</p>
    <p>Вы запросили обновление своего адреса электронной почты.</p>
    <p>Для завершения обновления, введите следующий код подтверждения: <strong>{{ $details['code'] }}</strong></p>
    <p>Если вы не запрашивали обновление адреса электронной почты, проигнорируйте это сообщение или обратитесь в службу поддержки.</p>
    <p>С уважением,<br>Chamba Team</p>
</div>
</body>
</html>
