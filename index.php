<?php

require_once('env.php');

// --------------------------
// 天気情報をDBから取得
// --------------------------

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// データベースに接続
$db_link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 文字コード
mysqli_set_charset($db_link, 'utf8');

// 接続エラーの確認
if( mysqli_connect_errno($db_link) ) {
  $dbmsg = mysqli_connect_errno($db_link) . ' : ' . mysqli_connect_error($db_link);
}
else {
  $city = '東京';
  if (isset($_POST['city'])) {
    $city = $_POST['city'];
  }

  // 全件数取得
  $sql = "SELECT timedate, MAX(maxw), MIN(minw), city FROM weather WHERE city = '".$city."' GROUP BY timedate"; 
  $res = mysqli_query($db_link, $sql);
    
  if( $res ) {
    $total = $res->fetch_all();
  }
  // DBとの接続解除
  mysqli_close($db_link);
  
}


// --------------------------
// グラフデータ算出
// --------------------------

$date_list = '';
$max_list = '';
$min_list = '';
foreach ($total as $value) {
  //日付データ
  $date_list .= "'".str_replace('-', '/', $value[0])."',";
  //最高気温データ
  $max_list .= $value[1].',';
  //最低気温データ
  $min_list .= $value[2].',';
}
// 末尾のカンマ(,)の除去
$date_list = rtrim($date_list, ',');
$max_list = rtrim($max_list, ',');
$min_list = rtrim($min_list, ',');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
　<title>グラフ</title> 
</head>
<body>
<h1>折れ線グラフ (<?php echo $city; ?>)</h1>
  <canvas id="myLineChart"></canvas>
  <form action="" method="post">
    <button type="submit" value="東京" name="city">東京</button>
    <button type="submit" value="大阪" name="city">大阪</button>
    <button type="submit" value="那覇" name="city">那覇</button>
    <button type="submit" value="札幌" name="city">札幌</button>
    <button type="submit" value="新潟" name="city">新潟</button>
    <button type="submit" value="熊谷" name="city">熊谷</button>
    <button type="submit" value="旭川" name="city">旭川</button>
  </form>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
  <script>
    var ctx = document.getElementById("myLineChart");
    var myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [<?php echo $date_list; ?>],
      datasets: [
        {
          label: '最高気温(℃）',
          data: [<?php echo $max_list; ?>],
          borderColor: "rgba(255,0,0,1)",
          backgroundColor: "rgba(0,0,0,0)"
        },
        {
          label: '最低気温(℃）',
          data: [<?php echo $min_list; ?>],
          borderColor: "rgba(0,0,255,1)",
          backgroundColor: "rgba(0,0,0,0)"
        }
      ],
    },
    options: {
      title: {
        display: true,
        text: '気温推移 (<?php echo $total[0][3]; ?>)'
      },
      scales: {
        yAxes: [{
          ticks: {
            stepSize: 5,
            callback: function(value, index, values){
              return  value + '℃'
            }
          }
        }]
      },
    }
  });
  </script>
</body>
</html>
