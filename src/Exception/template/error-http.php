<body style="background: #F8F8FF;">
    <div style="background: #FFF; padding: 15px; font-family: sans-serif;">
        <p>
            <h1 style="color: #EE0000;">Http Client alert: error in code execution</h1>
        </p>
        <hr>
        <?php if(isset($type)): ?>
        <p><strong>Type error: </strong> <?= $type; ?><br>
            <hr>
        </p>
        <?php endif; ?>
        <p><strong>Code: </strong><?= $code; ?><br>
            <hr>
        </p>
        <p><strong>Message: </strong><?= $msg; ?><br>
            <hr>
        </p>
    </div>
</body>