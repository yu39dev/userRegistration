<!--
    ユーザー登録システム要件定義
    1.ユーザー登録はユーザー情報をフォームから入力でき、DBへ情報が保存されること
    2.ユーザー登録時の入力情報は、「email」「パスワード」とする
    3.パスワードは、間違えて入力されることを防ぐ為に再入力させること
    4.パスワードは６文字以上かつ半角英数字のみとする
    5.ユーザー登録時に入力された情報に問題がある場合はエラーメッセージを表示する
    6.問題なくユーザー登録が終わったらマイページへ遷移する
-->

<!--
    ユーザー登録システムの全体像
    1.ユーザー登録画面から送信された情報をPHPで受け取る
    2.ユーザー登録画面で送信された情報をチェックする（バリデーションチェック）
    3.バリデーションチェックに問題があれば、エラーを表示させる
    4.バリデーションチェックに問題がなければ、DBへユーザー情報を保存する
    5.DBの登録後にマイページへ遷移させる
-->

<!--
    作成の流れ
    1.ユーザー登録フォーム画面とマイページの画面を作る
    2.バリデーションチェックのロジックを作る
    3.dbとテーブルを作る
    4.dbへ情報を保存するロジックを作る
-->

<!--今回は簡素化のため、サニタイズはなし-->

<?php
//画面にエラーを表示させるためのもの(https://www.php.net/manual/ja/function.error-reporting.php)(https://www.php.net/manual/ja/errorfunc.configuration.php#ini.display-errors)
error_reporting(E_ALL); //E_STRICTレベル以外のエラーを報告する
ini_set('display_errors', 'On'); //画面にエラーを表示させるか

//1.post送信されていた場合

//L194以降でPOST送信された情報が$_POSTに代入されて処理される
if (!empty($_POST)) { //!=でない、empty=空、=$_POSTが空でないとき=$_POSTに値が入ってるとき{}の処理を実行。L45の変数が定義されないので初めてページを開くときはエラーを表示しない。

    //エラーメッセージを定数に設定 定数とわかりやすいように大文字で
    define('MSG01', '入力必須です');
    define('MSG02', 'Emailの形式で入力してください');
    define('MSG03', 'パスワード（再入力）が合っていません');
    define('MSG04', '半角英数字のみご利用いただけます');
    define('MSG05', '6文字以上で入力してください');

    //配列$err_msgを用意(連想配列)
    $err_msg = array();

    //2.フォームが入力されていない場合
    if (empty($_POST['email'])) { //キーがemail。name属性の値をキーとして取得する。ここであればL201「name="email"」の箇所
        $err_msg['email'] = MSG01; //空であればMSG01を代入
    }
    if (empty($_POST['pass'])) {
        $err_msg['pass'] = MSG01;
    }
    if (empty($_POST['pass_retype'])) {
        $err_msg['pass_retype'] = MSG01;
    }
    if (empty($err_msg)) {
        //変数にユーザー情報を代入
        $email = $_POST['email'];
        $pass = $_POST['pass'];
        $pass_re = $_POST['pass_retype'];

        //3.emailの形式でない場合
        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)) { //必要に応じて「php 正規表現」「php regular expression」で検索。ここでは簡素化
            $err_msg['email'] = MSG02;
        }

        //4.パスワードとパスワード再入力が合っていない場合
        if ($pass !== $pass_re) {
            $err_msg['pass'] = MSG03;
        }

        if (empty($err_msg)) {
            //5.パスワードとパスワード再入力が半角英数字でない場合
            if (!preg_match("/^[a-zA-Z0-9]+$/", $pass)) {
                $err_msg['pass'] = MSG04;
 
            } elseif (mb_strlen($pass) < 6) {
                //6.パスワードとパスワード再入力が6文字以上でない場合
                $err_msg['pass'] = MSG05;
            }

            if (empty($err_msg)) {

                //DBへの接続準備 ここは型で覚える
                $dsn = 'mysql:dbname=php_sample01;host=localhost;charset=utf8';
                $user = 'root';
                $password = 'root';
                $options = array(
                    // SQL実行失敗時に例外をスロー
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // デフォルトフェッチモードを連想配列形式に設定
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
                    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                );

                // PDOオブジェクト生成（DBへ接続）
                $dbh = new PDO($dsn, $user, $password, $options); //PHPからDBに接続するためのオブジェクト（物体）をつくる（？）

                //SQL文（クエリー作成）
                $stmt = $dbh->prepare('INSERT INTO users (email,pass,login_time) VALUES (:email,:pass,:login_time)'); //クエリーを作るprepareメソッド。プレースホルダ（https://webukatu.com/wordpress/blog/23123/）関連：SQLインジェクション

                //プレースホルダに値をセットし、SQL文を実行
                $stmt->execute(array(':email' => $email, ':pass' => $pass, ':login_time' => date('Y-m-d H:i:s')));

                header("Location:mypage.php"); //マイページへ headerが遷移する関数で後ろにパス、もしくはURLを記述
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>ホームページのタイトル</title>
    <style>
        body {
            margin: 0 auto;
            padding: 150px;
            width: 25%;
            background: #fbfbfa;
        }

        h1 {
            color: #545454;
            font-size: 20px;
        }

        form {
            overflow: hidden;
        }

        input[type="text"] {
            color: #545454;
            height: 60px;
            width: 100%;
            padding: 5px 10px;
            font-size: 16px;
            display: block;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        input[type="password"] {
            color: #545454;
            height: 60px;
            width: 100%;
            padding: 5px 10px;
            font-size: 16px;
            display: block;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            border: none;
            padding: 15px 30px;
            margin-bottom: 15px;
            background: #3d3938;
            color: white;
            float: right;
        }

        input[type="submit"]:hover {
            background: #111;
            cursor: pointer;
        }

        a {
            color: #545454;
            display: block;
        }

        a:hover {
            text-decoration: none;
        }

        .err_msg { /*エラーメッセージを赤色で表示する*/
            color: #ff4d4b;
        }
    </style>
</head>

<body>

    <h1>ユーザー登録</h1>
    <!--送信先と送信方法をそれぞれactionとmethodを用いて指定。
    actionは送信先（他のファイルに送信する場合に用いる（今回のような自分自身宛*の場合は省略可能））
    methodはpostかgetを指定。基本はpost
    *自分自身というのは「送信」した後、もう一度このファイルが開かれて上の行から処理がされる。送信した情報は$_POSTの中に入る
    -->
    <form action="" method="post">
        <span class="err_msg"><?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?></span> <!--エラーメッセージを表示するためのもの-->
        <input type="text" name="email" placeholder="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">

        <span class="err_msg"><?php if (!empty($err_msg['pass'])) echo $err_msg['pass']; ?></span> <!--エラーメッセージを表示するためのもの-->
        <input type="password" name="pass" placeholder="パスワード" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>"> //type属性をpasswordにするとパスワード専用のフォームになる（入力時に***てなるやつ）

        <span class="err_msg"><?php if (!empty($err_msg['pass_retype'])) echo $err_msg['pass_retype']; ?></span> <!--エラーメッセージを表示するためのもの-->
        <input type="password" name="pass_retype" placeholder="パスワード（再入力）" value="<?php if (!empty($_POST['pass_retype'])) echo $_POST['pass_retype']; ?>">

        <input type="submit" value="送信">
    </form>
    <a href="mypage.php">マイページへ</a>
</body>

</html>