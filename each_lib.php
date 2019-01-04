<?php
/****************************************************/
/*  include file for jportal     修正テスト  ブランチ */
/****************************************************/

  header('Content-Type: text/html; charset=Shift_JIS');


  $DBSERVER   = "sddb0040058795.cgidb";    //MySQLサーバー名
  $DBUSER     = "sd_dba_NjkwNTU5";         //ログインユーザー名
  $DBPASSWORD = "ryomiyo#2008";             //パスワード
  $DBNAME     = "sddb0040058795";   //データベース名

  $PICTUREDIR   = "picture/";       //写真ファイルの最終保存先パス
  $PICTURETMP   = "picturetmp/";    //写真ファイルの一時保存先パス

  $FILEDATADIR   = "filedata/";       //ファイルの最終保存先パス
  $FILEDATATMP   = "filedatatmp/";    //ファイルの一時保存先パス

  $ADMSESS    = "sslogined";    //管理者ログインで使うセッション変数名
  $USERSESS    = "sslogined";    //ログインで使うセッション変数名

function newinfo_check() {

  $usersess = $_SESSION['USERSESS'];

  $upddate_from_a = date("Y-m-d",strtotime('-1 day')); //過去日の指定
  $upddate_from_b = date("Y-m-d",strtotime('-7 day')); //過去日の指定
  $upddate_to   = date("Y-m-d");                     //当日の設定

  //名前表示パターン
  $mysqli = my_sql_connect("sjis");

  //個人の検索可能情報区分を取得
  $searchclass = perinfoclass_set($usersess);

  //登録日別検索の場合は、アクセスできる全てを表示
  $infoclass = ALL; 

  //登録日別検索のWHERE句を作成(開始)
  $where = " ((infoavaflg != '削除') and ";

  $where .= "(";
  for ($i = 0; $i < sizeof($searchclass); $i++) {
    if ($i > 0 ) {
      $where .= " or ";
      $keynaviso .= ",";
    }
    $sql_cl = "select * from tblclass where class_name = \"$searchclass[$i]\" ";
    //dp(sql_cl,$sql_cl);
    $rst_cl = $mysqli->query($sql_cl);
    $colcl = mysqli_fetch_array($rst_cl);
       
    $class_id_v = $colcl['class_id'];
    if ( $searchclass[$i] == "個人" ) {
      $where .= " (infoclass = \"$searchclass[$i]\" and regid = \"$usersess\") ";
      $keynaviso .= $searchclass[$i] ;
    }
    else {  
      $where .= " (infoclass = \"$searchclass[$i]\" and class_id = \"$class_id_v\"  ) ";
    }
  }

  $where .= ") and ";
  $where_a .= " (upddate BETWEEN \"$upddate_from_a\" AND \"$upddate_to\" )) "; //期間指定の条件設定
  $where_b .= " (upddate BETWEEN \"$upddate_from_b\" AND \"$upddate_to\" )) "; //期間指定の条件設定

  $sql = "select Count(*) AS cnt from tblinfo where $where" . $where_a;
  //SQL文を発行します
  $mysqli->query($sql);
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  $tcnt_a = $col['cnt'];

  $sql = "select Count(*) AS cnt from tblinfo where $where" . $where_b;
  //SQL文を発行します
  $mysqli->query($sql);
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  $tcnt_b = $col['cnt'];

  mysqli_free_result($rst);

  //登録日別検索のWHERE句を作成(終了)
  return array($tcnt_a,$tcnt_b);

}


//指定したDBでのキーワード検索結果のbodyを返す関数
function body_keysearch_any(
	$db_con_info,$host_url,$host_name,$usersess,$userid,$search_opt,

	$searchclass,
	$get_searchclass,

	$get_keyword,
	$get_keyword_org,

	$get_page,
	$get_tcnt,

	$get_sorttype,
	$get_showtype,

	$get_orderby_opt,

	$get_infoclass,
	$get_dispclass,

	$get_classid,
	$get_catinfoclass,
	$get_categoryname,
	$get_categid,

	$get_regdate,

	$get_rireki_type,
	$get_click_rireki,$body_keys)  {

  //ログ書き込みフラグ　デフォルト off:書き込みなし
  $log_write_flg = "off"; // on:書き込む
  
  $keyword = $get_keyword;

  $page = $get_page;
  $tcnt = $get_tcnt;

  $sorttype = $get_sorttype;
  $showtype = $get_showtype;

  $orderby_opt = $get_orderby_opt;

  $infoclass = $get_infoclass;
  $dispclass = $get_dispclass;
  $catinfoclass = $get_catinfoclass;
  $keyword_org = $get_keyword_org;
  $categoryname = $get_categoryname;

  //classidの受け取りを追加
  $classid = $get_classid;
  $categid = $get_categid;

  $rireki_type = $get_rireki_type;
  //クリック履歴表示用変数
  $click_rireki = $get_click_rireki;

  //個人毎の検索情報表示区分のセット
  $perinfoclass = perinfoclass_set2($db_con_info,$usersess);

  //サーチの選択区分のセキュリティチェック（全ての区分が登録されているか照合チェック）
  $searchclass_chk_f = "OK"; 
  $searchclass_chk_cnt= 0;
  for ( $i=0 ; $i < sizeof($get_searchclass);$i++) { 
    for ( $j=0 ; $j < sizeof( $perinfoclass );$j++) {
      if ( $get_searchclass[$i] == $perinfoclass[$j] ) {  
        $searchclass_chk_cnt = $searchclass_chk_cnt + 1;
      }  
    }
  }
  
  if ( $searchclass_chk_cnt != sizeof( $get_searchclass ) ) { //指定された区分が全てない場合NG
    $searchclass_chk_f = "NG";
    $get_searchclass = $perinfoclass; //NGの場合は、個人の設定を強制セット
  } else { //指定した区分があった場合、get検索用パラメータbody作成
    $infoclass_body ="";
    for ( $i=0 ; $i < $searchclass_chk_cnt ; $i++) { 
      $infoclass_body .= "searchclass[" . $i . "]=" . $get_searchclass[$i] ; //サーチ区分の設定
      $last_chk = $i + 1; 
      if ( $last_chk < $searchclass_chk_cnt) { //ラストでなければ&を追加
        $infoclass_body .= "&";
      }
    }
  }    

  //サーチの選択区分のセッション間引継ぎ
  //指定された検索区分が他DBであるかチェック
  //合致している数をチェック

  $ser_flg = 0;
  for ( $i=0; $i < sizeof($perinfoclass);$i++) {
    for ( $j=0; $j < sizeof($get_searchclass);$j++) {
      if ( $perinfoclass[$i] == $get_searchclass[$j]) {
         $ser_flg++;
      }  
    }
  }  

  if ( $ser_flg == 1 ) { 
    $searchclass = hold_searchclass($perinfoclass,$searchclass, $get_searchclass);
  } else {
    //個人の検索情報区分をすべて設定
    $searchclass = hold_searchclass($perinfoclass,$searchclass, $perinfoclass);
  }

  //DBに接続
  $mysqli = my_sql_connect_any($db_con_info,"sjis") ;

  //個人の登録オプションを読み込む
  $sql = "SELECT * FROM user where userid = \"$usersess\" ";
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);

  $opt_ser_disp_menu = $col[opt_ser_disp_menu];//検索メニューの種類
  $opt_ser_disp_lev = $col[opt_ser_disp_lev];//検索表示のレベル
  $opt_ser_disp_num = $col[opt_ser_disp_num];//検索結果の表示件数
  $opt_ser_hist_num = $col[opt_ser_hist_num];//キーワード履歴件数

  //個人のウォッチワード有無確認
  if ( $search_opt == "watch") {
    $keyword     = $col['watchword'];//キーワード
    $keyword_org = $col['watchword'];//キーワード
  }  
  //dp(keyword_org,$keyword_org);

  //ログ用設定
//  $dispname = "search.php";
  $scene = "キーワードサーチ";

  if( !isset($infoclass)) {
    $infoclass = "ALL";
  } else{
    $infoclass = $get_infoclass;
  }

  if(!isset($keyword_org)) {
    $keyword_org = $keyword;
  } 
  if(!isset($keyword_org)) {
    $keyword_org = $get_keyword_org ;
  }
  //dp(keyword_org,$keyword_org);

/*
  //ウォッチワード表示の場合は、そのワードを取得
  if ($dispopt == "watch" ){ 
    $sql = "SELECT * FROM user WHERE userid = \"$usersess\" " ;

    //結果セットを取得します
    $rst = $mysqli->query($sql);
    $col = mysqli_fetch_array($rst);

    $keyword_org = $col['watchword'];
    $keyword = $col['watchword'];

    if ( strlen($keyword) != 0 ) {
      $searchtype = "kw";
    }
    //ログ用設定
    $dispname = "watchword";
    $scene = "ウォッチワード";
  }
*/

  //１ページ当りの表示件数を設定
  $PAGESIZE = $opt_ser_disp_num;

  //本文作成
  if ( $search_opt == "watch" and $keyword !="" ) {
    $body_result_title = "<h3>ウォッチワード情報</h3><a href='userupd.php#watchword' align='right' target='_blank' style='text-decoration:none;'>ウォッチワード登録はこちら</a>" ;
  } else {
    $body_result_title = "<h3>最新登録情報</h3><a href='userupd.php#watchword' align='right' target='_blank' style='text-decoration:none;'>ウォッチワード登録はこちら</a></p>" ;
  }
  
  $body .= "<div id='hpb-wrapper'><div id='hbp-main'>" . $body_result_title . ""; 
//  $body .= "<div id='hpb-inner'><div id='hpb-wrapper'><div id='hbp-main'>" . $body_result_title . "";  リンク表示できない
//  $body .= "<div id='hpb-main'><h3>【　" . $host_name . "　】</h3>";

  //情報区分を選択するHTMLを生成
  list($body_tmp,$search_class_chk) = body_search_class_any($host_url,$rireki_type,$perinfoclass,$searchclass);
  $infodiv_body = $body_tmp;
  
  //検索表示の窓の部分のHTMLを生成
  $searchwindow = body_search_windows_any($db_con_info,$host_url,$host_name,$usersess,$dispopt,$opt_ser_disp_menu,$opt_ser_hist_num,$keyword_org);

  //検索の種類を判別
  //  if (isset($keyword) and strlen($keyword) >= 0 and strlen($categoryname) == 0 ) {
    
  //クリック履歴検索
  if (isset($get_click_rireki)) {

    $searchtype = "cr";//検索タイプをクリック履歴検索に設定
    $resultmes = "　クリック履歴で";

  } else {
  //キーワード検索
    $searchtype = "kw";//検索タイプをキーワード検索に設定

    //不正要求の場合はメッセージを出し、ログ出力
    if ( $searchclass_chk_f == "NG") {
      print htmlheader("検索エラー");
      print "不正なアクセスです。情報区分は個人で表示されているもの以外は指定できません。";
      $result = "不正アクセス";
      $comment = "不正な情報クラスでのアクセスがありました。情報区分: ";
      for ( $i=0 ; $i < sizeof($get_searchclass);$i++) { 
        $comment .=  $get_searchclass[$i] . ";" ;
      }
      if ( $log_write_flg == "on" ) {
        logw($userid,$dispname,$scene,$searchtype,$result,$tcnt,$keyword_org,$categid,$regdate,$comment);
      }
      $comment = "";
      header("location: login.php?id=3");
      exit();
    }

    //検索結果のメッセージ作成
    $resultmes = "情報区分："; 
    if (strlen($keyword) == 0 ) {
      if ( isset($searchclass[0]) ) { //カテゴリを検索したキーワードなしの場合
        $kw_opt = "ALL";
        for ($i = 0; $i < sizeof($searchclass); $i++) {
          $resultmes .= "「" . $searchclass[$i] . "」"; 
        }
        $resultmes .= "で";
        $result = "部分表示";
      } else {
        $resultmes .= "　全件で";
        $result = "全件表示";
      }
      $result = "全件表示";
      if ( $log_write_flg == "on" ) {
        logw($userid,$dispname,$scene,$searchtype,$result,$tcnt,$keyword_org,$categid,$regdate,$comment);
      }
      
    } else { 
      //キーワードを表示するbodyを生成
      $kw_opt = "";
      for ($i = 0; $i < sizeof($searchclass); $i++) {
        $resultmes .= "「" . $searchclass[$i] . "」"; 
      }
      $resultmes .= "　キーワード：「" . $keyword_org . "」で";
    }


  }
  //dp(searchtype,$searchtype);

  //検索の種類に応じてWHERE条件を組み立て
  switch ($searchtype) {

    //キーワード検索のWHERE
    case "kw":

      $research_opt = "infoclass=" . $infoclass . "&dispclass=". $dispclass . "&keyword=". $keyword;

      //キーワード空白の場合、全件表示
      if ( $kw_opt == "ALL" ) { 
        $where = " WHERE ((infoavaflg != '削除') and ";

        $where .= "(";
        for ($i = 0; $i < sizeof($searchclass); $i++) {
          if ($i > 0 ) {
            $where .= " or ";
            $keynaviso .= ",";
          }

          $sql_cl = "select * from tblclass where class_name = \"$searchclass[$i]\" ";
          $rst_cl = $mysqli->query($sql_cl);
          $colcl = mysqli_fetch_array($rst_cl);
           
          $class_id_v = $colcl['class_id'];
          
          if ( $searchclass[$i] == "個人" ) {
            $where .= " (infoclass = \"$searchclass[$i]\" and regid = \"$usersess\") ";
            $keynaviso .= $searchclass[$i] ;
          }
          else {  
            $where .= " (infoclass = \"$searchclass[$i]\" and class_id = \"$class_id_v\") ";
            $keynaviso .= $searchclass[$i] ;
          }   
        }
        $where .= ") )";

      } else { //キーワードが空白でなければキーワードを配列に格納する 

        //キーワードからエスケープ文字を取り除く
        //文字化け対応のためコメントアウト
        //$keyword = stripcslashes($keyword);

        //キーワードの前後のスペースを取り除く
        $keyword = trim($keyword);
      
        //全角スペースの半角変換と半角カナの全角変換
        $keyword = mb_convert_kana($keyword, "sKV", "SJIS");

        //半角文字の全角文字化、全角文字の半角文字化を検索対象として追加(2017.9.9追加）
        $keyword_h = mb_convert_kana($keyword, "a", "SJIS");
        $keyword_z = mb_convert_kana($keyword, "A", "SJIS");
        
        //キーワードをカンマかスペースで分解して配列に代入
        if(!strrchr($keyword, " ")){

          //キーワードに半角スペースが含まれていない場合
          $keyword = str_replace("、", ",", $keyword);
          $keyword_h = str_replace("、", ",", $keyword_h);
          $keyword_z = str_replace("、", ",", $keyword_z);

          $keyword = str_replace("，", ",", $keyword);
          $keyword_h = str_replace("，", ",", $keyword_h);
          $keyword_z = str_replace("，", ",", $keyword_z);

          $arykey = explode(",", $keyword);
          $arykey_h = explode(",", $keyword_h);
          $arykey_z = explode(",", $keyword_z);

          $tmpkey = "Or";

        } else {

          //キーワードに半角スペースが含まれている場合
          $arykey = explode(" ", $keyword);
          $arykey_h = explode(" ", $keyword_h);
          $arykey_z = explode(" ", $keyword_z);

          $tmpkey = "and";

        }

        //分解された各キーワードが空でないかチェック
        for ($i = 0; $i < sizeof($arykey); $i++) {
          if (strlen($arykey[$i]) == 0) {
            //分解されたキーワードのいずれかが空の場合
            $body .= "キーワードの指定が正しくありません！
                  <INPUT id='button_m' type='button' value='検索へ戻る'
                  onclick='window.location=\"searchmenu.php\"'>";
            print htmlheader("検索結果") .  $body . $infodiv_body . $searchwindow . htmlfooter();
            exit();
          }
        }

        //キーワード検索のWHERE句を作成(開始)
        $where = " WHERE ((infoavaflg != '削除') and ";

        $where .= "(";
        for ($i = 0; $i < sizeof($searchclass); $i++) {
          if ($i > 0 ) {
            $where .= " or ";
            $keynaviso .= ",";
          }

          $sql_cl = "select * from tblclass where class_name = \"$searchclass[$i]\" ";
          $rst_cl = $mysqli->query($sql_cl);
          $colcl = mysqli_fetch_array($rst_cl);
           
          $class_id_v = $colcl['class_id'];

          $search_class = $searchclass[$i];
          if ( $searchclass[$i] == "個人" ) {
            $where .= " (infoclass = \"$searchclass[$i]\" and regid = \"$usersess\") ";
            $keynaviso .= $searchclass[$i] ;
          }
          else {  
            $where .= " (infoclass = \"$searchclass[$i]\" and class_id = \"$class_id_v\" ) ";
          }
        }

        $where .= ") and (";
          
        $where .= "( "; //キーワードの検索用where句生成

        for ($i = 0; $i < sizeof($arykey); $i++) {
          if ($i > 0 ) {
            $where .= " " . $tmpkey;
          }
          $where .= " (
                      (infotitle Like \"%$arykey[$i]%\") or
                      (infoclass Like \"%$arykey[$i]%\") or
                      (infokind Like \"%$arykey[$i]%\") or
                      (infotag Like \"%$arykey[$i]%\") or
                      (comment Like \"%$arykey[$i]%\") or
                      (uriinfo Like \"%$arykey[$i]%\") or 
                      (categoryinfo Like \"%$arykey[$i]%\") or
                      (filedataname Like \"%$arykey[$i]%\") 
                    )";

          }
 
        $where .= ") or ( ";  //半角変換したキーワードでの検索用where句生成

        for ($i = 0; $i < sizeof($arykey_h); $i++) {
          if ($i > 0 ) {
            $where .= " " . $tmpkey;
          }
          $where .= " (
                      (infotitle Like \"%$arykey_h[$i]%\") or
                      (infoclass Like \"%$arykey_h[$i]%\") or
                      (infokind Like \"%$arykey_h[$i]%\") or
                      (infotag Like \"%$arykey_h[$i]%\") or
                      (comment Like \"%$arykey_h[$i]%\") or
                      (uriinfo Like \"%$arykey_h[$i]%\") or 
                      (categoryinfo Like \"%$arykey_h[$i]%\") or
                      (filedataname Like \"%$arykey_h[$i]%\") 
                    )";

          }

        $where .= ") or ( "; //全角変換したキーワードでの検索用where句生成

        for ($i = 0; $i < sizeof($arykey_z); $i++) {
          if ($i > 0 ) {
            $where .= " " . $tmpkey;
          }
          $where .= " (
                      (infotitle Like \"%$arykey_z[$i]%\") or
                      (infoclass Like \"%$arykey_z[$i]%\") or
                      (infokind Like \"%$arykey_z[$i]%\") or
                      (infotag Like \"%$arykey_z[$i]%\") or
                      (comment Like \"%$arykey_z[$i]%\") or
                      (uriinfo Like \"%$arykey_z[$i]%\") or 
                      (categoryinfo Like \"%$arykey_z[$i]%\") or
                      (filedataname Like \"%$arykey_z[$i]%\") 
                    )";

          }

          $where .= ") ";

          $where .= "))";
          //キーワード検索のWHERE句を作成(終了)
        }
 
      break;

    //クリック履歴検索のWHERE
    case "cr":

      $research_opt = "&click_rireki=" . $click_rireki ;

      //履歴表示件数
      $rc = 5;
      
      $sql = "SELECT * FROM tblsearchlog WHERE ( userid = '$userid') and  ( scene = 'リンククリック') ORDER BY serno  desc LIMIT 0,$rc";
      $sqlcnt = "SELECT Count(*) AS cnt from tblsearchlog WHERE ( userid = '$userid') and ( scene = 'リンククリック')";
       
      $mysqli = my_sql_connect_any($db_con_info,"sjis") ; 

      $rst = $mysqli->query($sql);
      $rstcnt = $mysqli->query($sqlcnt);

      $colcnt = mysqli_fetch_array($rstcnt);
      $tcnt = $colcnt['cnt'];

      //dp(tcnt,$tcnt);

      $i = 1;
      $where = " where ";
      while ($col = mysqli_fetch_array($rst)) {
        $where .= " infoid = " . $col['infoid'] ;
        if ( $i < $rc ) {
          $where .= " or ";
        }
        else  {
          $where .= "";
        }
        $i = $i + 1;
      }
    break;

  }
  //dp(where,$where);
  
  //並び順（ORDER BY）の生成
  if (!isset($sorttype) or $sorttype == 1) {
    //はじめて呼ばれたときまたはカテゴリ順指定のとき
    //登録日の最新順指定に変更 時間を追加
    $orderby = " ORDER BY upddate desc,updtime desc, tblinfo.categoryid, infoid";
  }
  else {
    //登録日順で指定（デフォルト）
    //時間を追加
    $orderby = " ORDER BY upddate desc,updtime desc, infoid";
  }
  if ( $click_rireki == "cr" ) {
    //指定なしの場合 serno をソート順に指定
    $orderby = " ORDER BY serno desc ";
  }

  
  //並び順の再検索判断
  if ( $orderby_opt == click and $click_rireki == "cr" ) {
    $orderby = " ORDER BY clickcnt desc,infovaluep desc,upddate desc,updtime desc, tblinfo.categoryid, infoid , tblsearchlog.serno desc "; //同じクリック数なら評価多い順 に加えて新しい順
  } else if ( $orderby_opt == click ) { 
    $orderby = " ORDER BY clickcnt desc,infovaluep desc,upddate desc,updtime desc, tblinfo.categoryid, infoid"; //同じクリック数なら評価多い順
  }

  //並び順の再検索判断
  if ( $orderby_opt == valuep ) {
    //同じ評価ポイントならクリック多い順
    $orderby = " ORDER BY infovaluep desc,clickcnt desc,upddate desc,updtime desc, tblinfo.categoryid, infoid";  }

  if (!isset($page)) {

    //初めて呼ばれたときは総件数を取得
    $sql = "SELECT Count(*) AS cnt FROM tblinfo" . $where;

    if ( $searchtype != "cr" ) {
      $rst = $mysqli->query($sql);
      $col = mysqli_fetch_array($rst);
      $tcnt = $col['cnt'];
      //dp(tcnt2,$tcnt);
      mysqli_free_result($rst);
    } else {
      //履歴タイプの場合は、カウントのSQLを変更
      $sqlcnt = "SELECT Count(*) AS cnt from tblsearchlog WHERE (userid = '$userid') and ( scene = 'リンククリック')";
      $rstcnt = $mysqli->query($sqlcnt);
      $colcnt = mysqli_fetch_array($rstcnt);
      $tcnt = $colcnt['cnt'];
      //dp(tcnt3,$tcnt);
      mysqli_free_result($rstcnt);
    }

    //該当件数をチェック
    if ($tcnt == 0) {

      //ログ書き込み
      $result = "検索キーワードなし";
      if ( $log_write_flg == "on" ) {
        logw($userid,$dispname,$scene,$searchtype,$result,$tcnt,$keyword_org,$categid,$regdate,$comment);
      }
      //http,https,fileであれば新規登録メニューへ
      if ( substr($keyword_org,0,5) == "http:" or substr($keyword_org,0,6) == "https:" or substr($keyword_org,0,5) == "file:") {

        //対象ページのタイトルがあればそれをinfotitleに入れ、infokindをwebで初期設定
        if ( getPageTitle( $keyword_org ) != "" ) {
          $infotitle = curl_get_contents( $keyword_org,60 );
          $infokind = "web";
        }

        //対象ページのメタタグがあればそれをinfotagに入れ初期設定
        $array_tag = get_meta_tags( $keyword_org );
        if ( $array_tag['keywords'] != ""  ) {
          $infotag = htmlspecialchars($array_tag['keywords'], ENT_COMPAT);
          $infotag = mb_convert_encoding($infotag,'Shift_JIS','auto');
        }
        if ( $array_tag['description'] != ""  ) {
          $comment = htmlspecialchars($array_tag['description'], ENT_COMPAT);
          $comment = mb_convert_encoding($comment,'Shift_JIS','auto');
        }
        $body .= "<br>検索条件に一致する情報はありませんでした。　
                 <INPUT id='button_m' type='button' value='戻る' 
                 onclick='history.back()'>　　　<A href='infonew.php?urlinfo=$keyword_org&infotitle=$infotitle&infokind=$infokind&infotag=$infotag&comment=$comment'>当リンク情報を新規登録</a><br>" . $infodiv_body. $searchwindow ;

        $body .= "<meta http-equiv='refresh' content='0; url=infonew.php?urlinfo=$keyword_org&infotitle=$infotitle&infokind=$infokind&infotag=$infotag&comment=$comment'>";
        
      } else {
        $body .= "<br>検索条件に一致する情報はありませんでした。　
                 <INPUT id='button_m' type='button' value='戻る' 
                 onclick='history.back()'>";
        $infotitle = "キーワード「" .  $keyword_org . "」 検索したけどありませんでした";
        $infokind = "未分類";
        $infoclass = "共通";
        $categoryid = 33;
        $comment = " 何を探していたか簡単に教えてください。改善と検索の支援をします。" ; 
        $body .= "　　　<A href='infonew.php?infotitle=$infotitle&infokind=$infokind&infotag=$infotag&comment=$comment&?categoryid=$categoryid'>見つかりませんでした！登録</a><br>";
        $body .= $infodiv_body . $searchwindow ;

      } 

      $search_result = "nothing";
    }
    //現在ページを初期設定
    $page = 1;
  }

  if ($search_result != "nothing") {
  //総ページ数を計算
  $totalpage = ceil($tcnt / $PAGESIZE);

  //ページ上部の表示を組み立て
  $body .= "<SCRIPT language='JavaScript'><!--
            function EditExec(infoid) {
              document.mainfrm_" . $host_name . ".action = 'infoupd.php';
              document.mainfrm_" . $host_name . ".infoid.value = infoid;
              document.mainfrm_" . $host_name . ".submit();
            }
            function DeleteCheck(infoid) {
              if(confirm('本当に削除していいですか？')){
                document.mainfrm_" . $host_name . ".action = 'infoupdexec.php';
                document.mainfrm_" . $host_name . ".infoid.value = infoid;
                document.mainfrm_" . $host_name . ".proc.value = 'del';
                document.mainfrm_" . $host_name . ".submit();
              }
            }
            // --></SCRIPT>";

 
//  $body .= $resultmes . "　$tcnt 件の情報が登録されています。 ";
  $body .= "<p align='left'>" . $resultmes . "　$tcnt 件の情報が登録されています。 ";
  $body .= "[" . ($PAGESIZE * ($page - 1) + 1) . "-";

  if ($page < $totalpage) {
    //最終ページより前のページのとき
    $body .= ($PAGESIZE * $page) . "] を表示。<br>";
  }
  else {
    //最終ページのとき
    $body .= "$tcnt] を表示。<br>";
  }
  $body .= "</p>";

  //１ページ分だけ抽出するSQL文を組み立て
  if ( $click_rireki == "cr" ) {
    //クリック履歴表示の場合
    //内部結合でlogテーブルの一部だけをselectし、commentの競合問題を解決
    $sql = "SELECT tblinfo.*,tblsearchlog.serno,accdate,acctime
          FROM tblinfo inner join tblsearchlog
          on tblinfo.infoid=tblsearchlog.infoid where ( userid = '$userid') and  ( scene = 'リンククリック')" .
          $orderby .
          " LIMIT " . $PAGESIZE * ($page - 1) . ", $PAGESIZE";
  }
  else { 
  //新バージョン。tblinfoのcategoryinfoをそのまま利用。
  $sql = "SELECT * FROM tblinfo " . 
          $where . $orderby .
          " LIMIT " . $PAGESIZE * ($page - 1) . ", $PAGESIZE";
  }
  //dp(sql,$sql);

  //結果セットを取得
  $rst = $mysqli->query($sql);

  //ページ本文を組み立てます

  //検索窓を追加
  //$body .= $infodiv_body;
  //$body .= $searchwindow;

  $body .= "<FORM name='mainfrm_" . $host_name . "' method='POST'>";

  //ページのナビゲーションを追加 (共通部）

  //ページナビゲーションのパラメータを設定
  switch ($searchtype) {
    case "kw":
      $keynavi = "&keyword=" . urlencode($keyword);
      $keynavi .= "&infoclass=$infoclass";
      $keynavi .= "&dispclass=$dispflg";
      $keynavi .= "&keyword_org=$keyword_org";
      $keynavi .= "&orderby_opt=$orderby_opt";
      break;
    case "cr":
      $keynavi = "&click_rireki=$click_rireki";
      $keynavi .= "&orderby_opt=$orderby_opt";
    break;
  }
  if (isset($sorttype)) {
    $keynavi .= "&sorttype=$sorttype";
  }
  if (isset($showtype)) {
    $keynavi .= "&showtype=$showtype";
  }

  // 上部ナビゲーション用のHTML作成
  $body .= "<p align='left'>";
  if ($page > 1) {
    //2ページ以降の場合は[先頭]と[前]を表示
    $body_navi_1 = "<A href = '$PHP_SELF?page=1&tcnt=$tcnt$keynavi'>&lt;&lt;
                    先頭へ</A>&nbsp;&nbsp;&nbsp;
                    <A href = '$PHP_SELF?page=" . ($page - 1) . "&tcnt=$tcnt$keynavi'>&lt;
                    前の $PAGESIZE 件</A>&nbsp;&nbsp;&nbsp;
                    ";
    $body .= $body_navi_1;
  }
  if ($totalpage > 1 and $page < $totalpage) {
    //全部で2ページ以上あってかつ現在が最終ページより
    //前のときは[次]と[最後]を表示
    $body_navi_2 = "<A href = '$PHP_SELF?page=" . ($page + 1) . "&tcnt=$tcnt$keynavi'>
                    次の $PAGESIZE 件&gt;</A>&nbsp;&nbsp;&nbsp;
                    <A href = '$PHP_SELF?page=$totalpage&tcnt=$tcnt$keynavi'>
                    最後へ&gt;&gt;</A>
                    ";
    $body .= $body_navi_2;
  }
  $body .= "</p>";

  if (!isset($showtype) or $showtype == 1) {

    //ヘッダの作成
    $body .= "<TABLE><TR>";
    $body .= "<TH>情報区分／<br>カテゴリ</TH>
              <TH>内容</TH>
              <TH>リンク</TH>";

    //表示のパターンを判断
    if ($orderby_opt == click ) {
      $body .= "<TH><A href=search.php?$research_opt title='更新日の最新順で表示' >更新日順<br>
                <A href=search.php?$research_opt&orderby_opt=click title='閲覧数の多い順で表示' >▼閲覧数順<br>
                <A href=search.php?$research_opt&orderby_opt=valuep title='評価の多い順で表示' >評価数順
                </TH>";
    } else if ($orderby_opt == valuep ) {
      $body .= "<TH><A href=search.php?$research_opt title='更新日の最新順で表示' >更新日順<br>
                <A href=search.php?$research_opt&orderby_opt=click title='閲覧数の多い順で表示' >閲覧数順<br>
                <A href=search.php?$research_opt&orderby_opt=valuep title='評価の多い順で表示' >▼評価数順
                </TH>";
    } else {
      $body .= "<TH><A href=search.php?$research_opt title='更新日の最新順で表示' >▼更新日順<br>
                <A href=search.php?$research_opt&orderby_opt=click title='閲覧数の多い順で表示' >閲覧数順<br>
                <A href=search.php?$research_opt&orderby_opt=valuep title='評価の多い順で表示' >評価数順
                </TH>";
    }  

    $body .= "<TH>ACT</TH></TR>";


    //評価が押された場合の再検索条件をセット
    $infovaluep_research_opt = $research_opt . "&orderby_opt=valuep";

    //結果セットからデータをループで読み込み
    while($col = mysqli_fetch_array($rst)) {
      //各レコード内容を表示する表を組み立て

      //2017.9.9追記 マップ情報のカテゴリだけで何も登録されていない場合は、表示対象外とした 
      if (( $col['map_flg'] != 1 or ( $col['map_flg'] == 1 and ( $col['infotitle'] != "" or $col['uriinfo'] != "" ))) or $DBNAME != "jportal")  { 

        //キーワードが指定されているときはカテゴリのキーワードを太字に置換
        $tmpcategoryname = $col['categoryinfo'];
        if ($searchtype == "kw") {
          for ($i = 0; $i < sizeof($arykey); $i++) {
            $tmpcategoryname = ereg_replace(preg_quote($arykey[$i]), "<B>". "\\0" . "</B>", $tmpcategoryname);
          }
        }
 
        //タイトル、種別、タグより情報内容本文を作成
        $tmpcomment = "";
        if (strlen($col['infotitle']) > 0 ) {
          $tmpcomment .= $col['infotitle'] . "<br><br>";
        }

        //urlの/変換
        $uriinfo = str_replace('＼','/', $uriinfo);
 
        //\\、\の変換処理  \\のリンクの場合\を変換
        if ( substr($urlinfo,0,1) == "\\" ) {
          $urlinfo =  str_replace("\\\\","file://", $urlinfo); 
          $urlinfo =  str_replace("\\","/", $urlinfo);
        } else if ( substr($urlinfo,0,2) == "C:" or substr($urlinfo,0,2) == "D:" ) {
          $urlinfo =  str_replace("\\","/", $urlinfo);
          $urlinfo =  "file:///" .  $urlinfo; 
        }  

        //情報のアイコン表示：カテゴリに表示
        if ((strlen($col['infokind']) > 0) and ($col['infokind'] != '未分類' )) {
          $imgfilename = imgchk($col['infokind'],$imgfilename);
          if ( $col['uriinfo'] != "" ) {

            if ( substr($col['uriinfo'],0,5) == "http:" or substr($col['uriinfo'],0,5) == "file:") {  
              $tmpcategoryname .= "<br>" . "<A href=urilink.php?infoid=$col[infoid] target='_blank'> " .  $imgfilename ;
            } else {
              $tmpcategoryname .= "<br>" . "<A href='$col[uriinfo]' target='_blank'> " .  $imgfilename ;
            }
          } else {
            $tmpcategoryname .= "<br>" . $imgfilename ;
          }  
        }

        //情報のアイコン表示：区分表示する場合。共通区分以外はロックマーク表示。
        if ($col['infoclass'] != "共通" ) {
          if ( $col['uriinfo'] != "" ) {
            if ( substr($col['uriinfo'],0,5) == "http:") {  
              $tmpcategoryname .= "<br>" . "<A href=urilink.php?infoid=$col[infoid] target='_blank'><img src='lock16.png'> " ;
            } else {
              $tmpcategoryname .= "<br>" . "<A href='$col[uriinfo]' target='_blank'><img src='lock16.png'> " ;
            }
          } else {
            $tmpcategoryname .= "<br><img src='lock16.png'>";
          }
        }

        // イメージがあればアイコンを追加し、クリックして表示可能
        if ($col[infofilename] != "noimage.jpg" ) {
          $tmpcategoryname .= "<br><A href='$PICTUREDIR$col[infofilename]' target='_blank'>
                        <IMG src='image/picture.png'>";
        }

        //コメントのHTML生成
        $tmpcomment .=  $col['comment'] ;

        if (strlen($col['infotag']) > 0 ) {
          $tmpcomment .= "<br><br>タグ：" .  $col['infotag'];
        } 

        //キーワードが指定されているときは情報内容のキーワードを太字に置換
        if ($searchtype == "kw") {
          for ($i = 0; $i < sizeof($arykey); $i++) {
            $tmpcomment = ereg_replace(preg_quote($arykey[$i]), "<B>". "\\0" . "</B>", $tmpcomment);
          }
        }

        //ファイルデータがあればそのリンクを追加
        if (strlen($col['filedataname_int']) <> 0 ) {
          $filedataurl = $host_url . "/" . $FILEDATADIR . $col['filedataname_int'];
          $tmpcomment .= "<br>" .  "<A href='filedataurl' target='_blank' >" . $col['filedataname'] . "</A>";
        } 

        //キーワードが指定されているときはURLのキーワードを太字に置換
        $tmpuriinfo = $col['uriinfo'];
        if ($searchtype == "kw") {
          for ($i = 0; $i < sizeof($arykey); $i++) {
            $tmptmpuriinfo = ereg_replace(preg_quote($arykey[$i]), "<B>". "\\0" . "</B>", $tmpuriinfo);
          }
        }

        // 情報区分、カテゴリ名を表示
        $body .= "<TR>";
        $body .= "<TD width='100' align='left' valign='top' >$col[infoclass]/<br>$tmpcategoryname</TD>";

        //改行コードをBRタグに置換
        $tmpcomment = nl2br($tmpcomment);

        //簡易モード場合、表示内容を絞り込み
       if ($opt_ser_disp_lev == "simple" ) {
         $tmpcomment_full = $tmpcomment;
         $tmpcomment_array = str_split($tmpcomment,240);
         $tmpcomment = $tmpcomment_array[0];

         if ( isset($tmpcomment_array[1]) ) {
           $tmpcomment_full =  strip_tags($tmpcomment_full);
           $tmpcomment .= "　<A href='infodisplay.php?infoid=$col[infoid]' title='$tmpcomment_full' target='_blank'>･･さらに詳細</A>";
         } 
       }

       $body .= "<TD width='670' align='left' valign='top' style='word-break:break-all'>$tmpcomment</TD>";
       if ( $tmpuriinfo != null ) {
         $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'><A href=urilink.php?infoid=$col[infoid] target='_blank' title=$tmpuriinfo>クリック</A>";
       } else {
         $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>";
       }   
             
       if ( $searchtype == "cr" ) {
         $body .= "<br><br>" . $col['accdate'] . "<br>" . $col['acctime'] ; 
       }
                
       $body .= "</TD>";

       //クリック、評価ポイント表示セクション(S)

       //クリック数がある場合は表示
       if ( $col[clickcnt] != 0 ) {
         //評価ポイントがある場合は表示
         if ( $col[infovaluep] != 0 ) {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br><A href=urilink.php?infoid=$col[infoid] target='_blank'><img src='click16.png'>$col[clickcnt]</A><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'>$col[infovaluep]</A></TD>";
         }
         //評価ポイントがない場合
         else {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br><A href=urilink.php?infoid=$col[infoid] target='_blank'><img src='click16.png'>$col[clickcnt]</A><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'></A></TD>";
         }
       }
       //クリック数がない場合
       else {
         //評価ポイントがある場合は表示
         if ( $col[infovaluep] != 0 ) {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'>$col[infovaluep]</A></TD>";
         }
         //評価ポイントがない場合
         else {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'></A></TD>";
         }
       }

       //クリック、評価ポイント表示セクション(E)

       $body .= "<TD width='24' valign='top'>
                <INPUT id='button_sm' type='button' value='編集' onclick='EditExec(\"$col[infoid]\");'>
                <INPUT id='button_sm' type='button' value='削除' onclick='DeleteCheck(\"$col[infoid]\");'><br>
                <A href=infodisplay.php?infoid=$col[infoid] target='_blank'>詳細</a>
                </TD></TR>";

      } //2017.9.9追記 マップ情報のカテゴリだけで何も登録されていない場合は、表示対象外とした（終端）

    } //whileの終端

    $body .= "</TABLE>
            <INPUT type='hidden' name='infoid'>
            <INPUT type='hidden' name='proc'>
            <INPUT type='hidden' name='infoclass' value=\"$infoclass\" >
            <INPUT type='hidden' name='dispclass' value=\"$dispclass\" >
            <INPUT type='hidden' name='keyword_org' value=\"$keyword_org\" >
            <INPUT type='hidden' name='searchclass' value=\"$searchclass\" >
            <INPUT type='hidden' name='classid' value=\"$classid\" >
            <INPUT type='hidden' name='categid' value=\"$categid\" >
            <INPUT type='hidden' name='cateroryname' value=\"$cateroryname\" >
            </FORM>";

    }

    //結果セットを破棄します
    mysqli_free_result($rst);

    //検索キーワード(あった場合）のログ書き込みを追加
    $result = "検索ヒット";
    $scene  = "初期画面表示";
    if ( $log_write_flg == "on" ) {
      logw($userid,$dispname,$scene,$searchtype,$result,$tcnt,$keyword_org,$categid,$regdate,$comment);
    }
    //MySQLとの接続を解除します
    $mysqli->close();

    //ページのナビゲーションを追加(上部共通）
    $body .= "<p align='left'>";
    if ($page > 1) {
      $body .= $body_navi_1;
    }
    if ($totalpage > 1 and $page < $totalpage) {
      $body .= $body_navi_2;
    }
    $body .= "</p>";

  }
  $body .= "<p align='left'><a href='#'>このページの先頭へ</a></p></div>";

  return $body;
}


function per_keyword_disp_any($db_con_info,$host_url,$host_name,$userid,$dispopt,$rireki_cnt) {

  //パラメータオプション
  $dispopt = "per_keyword"; //使っていない

  $ROWSIZE = 10;

  //MySQLに接続します
  $mysqli = my_sql_connect_any($db_con_info,"sjis");

  $sql = "SELECT * FROM tblsearchlog WHERE ( userid = '$userid') and ( keyword != '' ) 
            and ( not ( keyword like 'http%')) 
            ORDER BY serno desc LIMIT 0 , $rireki_cnt";

  $rst = $mysqli->query($sql);

  $body .= "<style type='text/css'>
        #keytable_" . $host_name . " {
        table-layout: fixed;
        width: 100%;     
      }
      </style>";  

  //キーワード表示
  $body .= "<TABLE id='keytable_" . $host_name . "' >";
  $colnum = 1;
  while($col = mysqli_fetch_array($rst)) {
    if ($colnum == 1) {
      $body .= "<TR>";
    }
    //各レコード内容を表示する表を組み立てます
    $body .= "<TD align='left' valign='top' style='word-break:break-all'>
                <A href='search.php?dispclass=STA&keyword=$col[keyword]' target='_blank'>$col[keyword]</A>
                </TD>";
    if (++$colnum > $ROWSIZE) {
      //１行分表示したら次の行へ
      $body .= "</TR>";
      $colnum = 1;
    }
  }
  if ($colnum != 1) {
    $body .= "</TR>";
  }
  $body .= "</TABLE>";

  //結果セットを破棄します
  mysqli_free_result($rst);

  //MySQLとの接続を解除します
  $mysqli->close();

  //ページ本文を出力します
  return $body;

 }


//個人毎の情報区分で何が表示対象となっているか調べ、そのHTMLを生成
function body_search_class_any($host_url,$rireki_type,$perinfoclass,$searchclass) {

  $body = "<table><td  width='60'>";//テーブルの1カラム目の幅指定
  $body .= "<form name='kensaku' action='search.php' method='get'><A href='infoclass_disp_mnt.php' ><font color = 'black'>情報区分</font></A></td>";

  $body .= "</td><td width='840'>";//テーブルの2カラム目の幅指定

  //検索クラスの表示BODY($search_class_chk）作成
  if ( $rireki_type == "all" ) {
    for ($i = 0; $i < sizeof($perinfoclass); $i++) {
      $search_class_chk .=  "<input type='checkbox' name='searchclass[]' value=$perinfoclass[$i] checked><b>$perinfoclass[$i]</b>";
    }
  }
  else if ( $rireki_type == "clear" ) {
    for ($i = 0; $i < sizeof($perinfoclass); $i++) {
      $search_class_chk .=  "<input type='checkbox' name='searchclass[]' value=$perinfoclass[$i] >$perinfoclass[$i]";
    }
  }
  else {
    //検索クラスの選択
    for ($i = 0; $i < sizeof($perinfoclass); $i++) {
      //検索区分にチェックされたものの判定
      $flg = 0;
      for ($j = 0; $j < sizeof($searchclass); $j++) {
        if ($searchclass[$j] == $perinfoclass[$i] ) {
          $flg = 1;
        }
      }
      if ( $flg  == 1 ) {
          $search_class_chk .=  "<input type='checkbox' name='searchclass[]' value=$perinfoclass[$i] checked><b><a href='search.php?searchclass[0]=$perinfoclass[$i]&keyword= '>$perinfoclass[$i]</a></b>";
      }
      else {
          $search_class_chk .=  "<input type='checkbox' name='searchclass[]' value=$perinfoclass[$i] ><a href='search.php?searchclass[0]=$perinfoclass[$i]&keyword= '>$perinfoclass[$i]</a>";
      }
    }
  }

  $body .= $search_class_chk;//検索クラスの表示BODY付与
  $body .= "<br><A href='searchmenu.php?rireki_type=all'><font color = 'black'>全チェック</A>　　<A href='searchmenu.php?rireki_type=clear'><font color = 'black'>全クリア</A>";

  $body .=  "　　　<input id = 'button_sm' value='変更' type='submit'>";

  $body .= "</td></table>";

  return array($body,$search_class_chk);
}

//検索窓用のHTML本文
function body_search_windows_any($db_con_info,$host_url,$host_name,$usersess,$dispopt,$opt_ser_disp_menu,$opt_ser_hist_num,$keyword_org) {
  if ( $opt_ser_disp_menu == 'details') {
    $body .=  "　　表示レベル：<input type='radio' name='dispclass' value='STA' checked> 標準
                      <input type='radio' name='dispclass' value='ALL'>
                      <A title='イメージ／区分／登録ID表示を表示したい場合'>詳細</A><br>";
  }

  $body .= "<br>　　　<input size='51' name='keyword' type='text' id='keyword' value=\"$keyword_org\">
            <input id = 'button_m' value='　　検索　　' type='submit' class='withicon'>　";

$body .= "<style type='text/css'>
input.withicon {
   background-image: url('search_16.png');
   background-repeat: no-repeat;
   background-position: 2px center;
   padding-left: 21px;
}
</style>";

  //クリック履歴表示
  $body .= "<b>クリック履歴&nbsp;</b><A href='search.php?click_rireki=cr'><font color = 'black'>表示</A>";

  //キーワード履歴表示
  $body .="<b>　　検索履歴</b>";
  $body_rireki .= per_keyword_disp_any($db_con_info,$host_url,$host_name,$usersess,$dispopt,$opt_ser_hist_num);

  $body .="
  <a href='#' id='link_view_" . $host_name . "' onClick='toggle_view_". $host_name . "() ;return false;' >表示</a>
  <a href='#' id='link_hidden_" . $host_name . "' onClick='toggle_hidden_". $host_name . "();return false;' style='display:none'>非表示</a>";

/*
  $body .="
  <a href='#' id='link_view2' onClick='toggle_view2();return false;' >表示</a>
  <a href='#' id='link_hidden2' onClick='toggle_hidden2();return false;' style='display:none'>非表示</a>";
*/

  $body .= "　　　　<a href='user_option.php' target='_blank'>オプション設定</A>"; 

  $body .= "</form>";

  $body .= "
          <SCRIPT language='JavaScript'>
             document.getElementById('keyword').focus();
          </SCRIPT>";

  $body .="<div id='rireki_" . $host_name . "' style = 'display:none'>　$body_rireki</div>

  <script language='JavaScript' type='text/javascript'>
  <!--
  var elem2_1_". $host_name . " = document.getElementById('rireki_". $host_name . "');
  var elem2_2_". $host_name . " = document.getElementById('link_view_". $host_name . "');
  var elem2_3_". $host_name . " = document.getElementById('link_hidden_". $host_name . "');
  function toggle_view_". $host_name . "() {
    elem2_1_". $host_name . ".style.display = '';
    elem2_2_". $host_name . ".style.display = 'none';
    elem2_3_". $host_name . ".style.display = '';
  }
  function toggle_hidden_". $host_name . "() {
    elem2_1_". $host_name . ".style.display = 'none';
    elem2_2_". $host_name . ".style.display = '';
    elem2_3_". $host_name . ".style.display = 'none';
  }
  -->
  </script>
  ";

  return $body;
}






//個人ごとの検索区分情報を取得しセットする(新）
function perinfoclass_set2($db_con_info,$userid) {

  //MySQLに接続します
  $mysqli = new mysqli(
    $db_con_info[dbserver],
    $db_con_info[dbuser],
    $db_con_info[dbpassword],
    $db_con_info[dbname]
  );

  if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit;
  }
  $mysqli->set_charset("sjis"); // 文字化け防止

  $sql = "SELECT * from tblaccess where ( userid = '$userid' and avaflg != 'delete' ) ORDER BY sort_num ";
  $rst = $mysqli->query($sql);

  $perinfoclass[0] = "個人";
  $perinfoclass[1] = "共通";

  $i = 2 ;

  while($col = mysqli_fetch_array($rst)) {
    $perinfoclass[$i] = $col['class_name'];
    $i = $i + 1 ;
  }
   
  //結果セットを破棄します
  mysqli_free_result($rst);
  return $perinfoclass;
}


//DB接続関数：指定形、mysql文タイプ（PHPが古い場合版）
function my_sql_connect_old($dbserver,$dbuser,$dbname,$dbpassword,$charset) {
  $link = mysql_connect($dbserver,$dbuser,$dbpassword);
  mysql_select_db($dbname,$link);
}

//DB接続関数：指定形、mysqli文タイプ
function my_sql_connect_any($db_con_info,$charset) {
  $mysqli = new mysqli($db_con_info[dbserver],$db_con_info[dbuser],$db_con_info[dbpassword],$db_con_info[dbname]);
  if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit;
  }
  $mysqli->set_charset($charset); // 文字化け防止（sjis用)
  return $mysqli;
}

//セキュリティ対策 html文のタグ無効化
function h($str)
{
  return htmlspecialchars($str, ENT_QUOTES, 'sjis');
}





function body_common_header() {
  //トップページのヘッダ
  //セッションを開始します
  session_start();

  $dev_host_mes = "";
  //検証系の場合のメッセージ作成
  if( $_SERVER["HTTP_HOST"] == "10.90.233.52" ) {
    $dev_host_mes = "　　<font color='green'>検証用サイト</font>";
  }
   
  //名前表示パターン
  $mysqli = my_sql_connect("sjis");
  $usersess = $_SESSION['USERSESS'];
  //個人の名前のセット  負荷がかかる可能性があるので将来改善要
  $sql = "SELECT * FROM user where userid = '$usersess' ";
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  $namekanji = $col['namekanji'];//名前のセット
  $opt_disp_css = $col['opt_disp_css'];//CSSの表示タイプセット
  $per_osss_name = substr($col['mail_pc_address'], 0, strcspn($col['mail_pc_address'],'@'));//メールアドレスの名前部分セット
  $per_osss_url = "file://storage03.psss.g01.fujitsu.local/" . $per_osss_name;

  
  //ヘッダリンク情報body作成
//  $ate_mes['0'] = "組織再編手続き関連";
  $ate_mes['0'] = "MIS2事業本部ポータル";
  $ate_mes['1'] = "品質ポータル";
  $ate_mes['2'] = "SE支援ツール";
  $ate_mes['3'] = "個人用OSSS";
  $ate_mes['4'] = "4クラ事OSSS";
  $ate_mes['5'] = "2シス事OSSS";

//  $ate_url['0'] = "search.php?searchclass[]=共通&keyword=$ate_mes[0]' target='_blank'";
  $ate_url['0'] = "urilink.php?infoid=1331";
  $ate_url['1'] = "urilink.php?infoid=1747";
  $ate_url['2'] = "urilink.php?infoid=1404";
  $ate_url['3'] =  $per_osss_url;
  $ate_url['4'] = "urilink.php?infoid=2043";
  $ate_url['5'] = "urilink.php?infoid=1300";
  
  
  if (isset($_SESSION['USERSESS'])) {
//            $body_userid = "<table style='margin-left : auto ; margin-right : 0 ; text-align : 0;'><td>ユーザＩＤ</td><td> " .  $_SESSION['USERSESS']   . "</td></table>";

//  $body_userid = "<td>ID:</td><td> " .  $_SESSION['USERSESS']   . "さん</td>"; //ID表示パターン
    $body_userid = "<td></td><td> " .  $namekanji  . "さん</td>"; //名前表示パターン
  }

  $body = "
  <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
  <html lang='ja'>
  <head>
  <meta http-equiv='Content-Type' content='text/html; charset=Shift_JIS'>
  <meta http-equiv='Content-Style-Type' content='text/css'>
  <meta http-equiv='Content-Script-Type' content='text/javascript'>
  <meta name='keywords' content='情報ポータル'>
  <meta name='description' content='組織情報の検索ポータル'>
  <script type='text/javascript' src='js/topics.js'></script>
  <link rel='stylesheet' href='css/hpbparts.css' type='text/css' id='hpbparts' charset='Shift_JIS'>
  <link rel='stylesheet' type='text/css' href='css/style.css'>";
  
  if ( $opt_disp_css == "type1"  or $usersess == "" )  {
    $body .= "
    <link rel='stylesheet' href='css/container_3C_2c_top_type1.css' type='text/css' id='hpbcontainer' charset='Shift_JIS'>";
   $body .= "<link rel='stylesheet' href='css/main_jportal.css' type='text/css' id='hpbmain' charset='Shift_JIS'>"; 
  } else {
    $body .= "
    <link rel='stylesheet' href='css/container_3C_2c_top.css' type='text/css' id='hpbcontainer' charset='Shift_JIS'>
    <link rel='stylesheet' href='css/main_jportal.css' type='text/css' id='hpbmain' charset='Shift_JIS'>";
   }  
   $body .= "
  <link rel='stylesheet' href='css/user.css' type='text/css' id='hpbuser' charset='Shift_JIS'>
  <title>情報ポータル</title>

  </head>
  <body>

  <div id='container'>

  <!-- ページ開始 -->
  <div id='page'>

  <!-- ヘッダ開始 -->
  <div id='header'>

  <h1 class='siteTitle'><p>　情報ポータル". $dev_host_mes . "</P></h1>

  <ul class='guide'>
  <li class='first'><a href='site_mokuteki.php' target='_blank' style='text-decoration:none;'>当サイトについて</a></li>
  <li><a href='info_faq.php' style='text-decoration:none;'>FAQ</a></li>
  <li><a href='infostamap.php' style='text-decoration:none;'>サイトマップ</a></li>
  <li><a href='logout.php' style='text-decoration:none;'>ログアウト</a></li>
  <li>" . $body_userid . "</li>
  </ul>


  <ul class='nl clearFix'>
	<li class='active'><a href='login.php?id=1'>トップ</a></li>
	<li><a href='login.php?id=2'>マップ</a></li>
	<li><a href='login.php?id=3'>検索</a></li>
	<li><a href='login.php?id=4'>登録</a></li>
	<li><a href='extlink.php'>サイト</a></li>
	<li><a href='userupd.php'>設定</a></li>
  </ul>";

  //ヘッダリンク情報の組み込み
//  $body .= header_link_body();
  $body .= " 
  <ul class='spotlight'>";
  for ($i = 0; $i < sizeof($ate_mes); $i++) {
    $body .= "<li><a href='" . $ate_url[$i] . "' target='_blank' style='text-decoration:none;'>" . $ate_mes[$i] . "</a></li>";
  }
  $body .= "</ul>";

  $body .= "
  <hr class='none'>

  </div>
  <!-- ヘッダ終了 -->
  ";
  return array($body,$per_osss_url);  
}

//htmlheaderで読んでいる用に作成（リストで受けれないため）
function htmlheader($pagetitle) {
  list ($body_header,$per_osss_url) = body_common_header();
  return $body_header;
}

//トップの本体部分
function body_top_main($per_osss_url) {

  $usersess = $_SESSION['USERSESS'];

  //DBに接続
  $mysqli = my_sql_connect("sjis");

  $body = "

  <!-- コンテンツ開始 -->
  <div id='content'>

  <!-- メインカラム開始 -->
  <div id='main'>
  
  <div class='section topics'>

  <ul class='tabs clearFix' id='tabs'>
  <li class='first' id='tab1'><a href='#' onclick='topics(1); return false;'>検索サイト</a></li>
  <li id='tab2'><a href='#' onclick='topics(2); return false;'>シーン別検索</a></li>
  <li id='tab3'><a href='#' onclick='topics(3); return false;'>各種通知</a></li>
  <li id='tab4'><a href='#' onclick='topics(4); return false;'>各種状況</a></li>
  <li class='last' id='tab5'><a href='#' onclick='topics(5); return false;'>マイタグ</a></li>
  </ul>

  <div class='topicArea'>

  <!-- ボックス1開始 -->
  <div class='topic' id='topic1'>

  <p>【各種検索サイト】</p>
  <!-- <p>各種検索サイト</p>  -->
  <ul>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/' target='_blank' style='text-decoration:none;'>IKBトップ</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/itimap' target='_blank' style='text-decoration:none;'>IKB-ITIMAP</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/cloud.aspx' target='_blank' style='text-decoration:none;'>IKB-クラウド</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/metaarc.aspx' target='_blank' style='text-decoration:none;'>IKB-グランドデザイン</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://dds-pre.b.css.fujitsu.com/sisearch/' target='_blank' style='text-decoration:none;'>SE資材サーチ</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://s-navi.solnet.ssg.fujitsu.com/' target='_blank' style='text-decoration:none;'>SE情報ポータル</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://mysite.gcs.g01.fujitsu.local/personal/search/Pages/advanced.aspx?k=ALL%28%E4%B8%AD%E5%8E%9F%E3%83%93%E3%83%AB%29%20%28Path%3A%22ikb%2Esolnet%2Essg%2Efujitsu%2Ecom%2F%22%20OR%20Path%3A%22s%2Dnavi%2Esolnet%2Essg%2Efujitsu%2Ecom%2F%22%29' target='_blank' style='text-decoration:none;'>富士通全社検索</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://www.google.co.jp/' target='_blank' style='text-decoration:none;'>google</a><br>
  </ul>


  </div>
  <!-- ボックス1終了 -->

  <!-- ボックス2開始 -->
  <div class='topic' id='topic2'>
  <p>【シーン別各種検索】</p>
  <!--
  <p>ボックス（本文領域）の高さは11emに設定されています。一番高さのあるボックスに合わせて調整してください。</p>
  <p>内容がボックスの高さからはみ出た場合は、スクロールで表示されることになります。（「タブ説明3」を参照）</p>
  -->
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;今後提供予定<br>
  
  </div>
  <!-- ボックス2終了 -->
  
  <!-- ボックス3開始 -->
  <div class='topic' id='topic3'>

  <!--
  <p>このサンプルでは、タブエリアの幅いっぱいにタブを並べています。タブエリアの幅（468px）を5で割り切れないので、最初と最後の項目にクラス名（first、last）を設定して項目幅を調整しています。（割り切れる場合はこのクラス名の設定は必要ありません）</p>
  <p>幅いっぱいに並べない（右側に余白が残る）場合は、CSSコード内の「最後のリンクエリア」の設定を削除してください。（これを削除しないと、最後のタブの右垂直線が表示されません）</p>
  <p>また、タブ内のテキストの左右には余裕を持たせてください。ギリギリに設定すると、ブラウザの文字サイズを大きくした際に折り返しが入ってしまいます（表示が崩れてしまいます）。</p>
  <p>ブラウザの文字サイズを大きくして表示確認を行ってください。</p>
  -->

  <a>【組織内での通知情報及びリンク先を掲載】</a>
  <ul>
<!--  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='infodisplay.php?infoid=531' target='_blank'>【プロセス改善】商談検討会/PA会プロセス見直し</a><br> -->
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/itkibanbg/Lists/BG' target='_blank'>旧西BG)IT本部ポータル通知事項</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/fwest/portal/Lists/fwBBS' target='_blank'>旧西BG)ポータル通知事項</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/jst/recognization_gyomu/Lists/List/AllItems.aspx' target='_blank'>10月1日付組織再編ポータル お知らせ（掲示板）</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/itsolution/ITSBUPortal/Lists/News/view2.aspx' target='_blank'>ITシステム事業本部 : 事業本部お知らせ </a><br>
  </ul>
  </div>
  <!-- ボックス3終了 -->
  
  <!-- ボックス4開始 -->
  <div class='topic' id='topic4'>
  <a>【組織及び個人の各種状況及びリンク先を掲載】</a>
  <ul>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='portforio_map_main.php' target='_blank'>2017年度 第二シス事　下期LCM(最新状況)</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki_tougetu.php' target='_blank'>2017年度 SI事業部　上期損益実績(最新状況)</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki.php' target='_blank'>2013-2016年度　SI事業部　損益実績</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki_cs_ruikei_ranking.php?sorttype=ruikei_uriage' target='_blank'>2014-2017年度　SI事業部　顧客別実績ランキング</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='portforio_dash0.php' target='_blank'>事業部　ポートフォリオ</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='portforio_dash0_gyousyu1.php' target='_blank'>事業部　業種別ビジネス状況</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki_dash_disp_chart.php' target='_blank'>SI事業部　2016年度実績（グラフあり）</a><br>
  
  </ul>

  </div>
  <!-- ボックス4終了 -->


  <!-- ボックス5開始 -->
  <div class='topic clearFix' id='topic5'>

  <!--
  <div class='catch'>
  <p><a href='#'><img src='noimage.jpg' alt='サンプル' width='100' height='70'></a></p>
  <p><a href='#'>キャプション</a></p>
  </div>
  --> 

  <div class='text'>
  <p>【個人のリンク情報】</p>
  <!-- <p>ボックス内の右側に写真を配置することもできます。（写真の幅は128pxまで）</p>  -->

  <ul>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='mylink_display.php' target='_blank'>マイお気に入り</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='" . $per_osss_url . "' target='_blank'>マイフォルダ（個人osssフォルダ）</a><br>
 
  </ul>
  </div>

  </div>
  <!-- ボックス5終了 -->

  </div>

  <!-- ↓タブボックスを使用する場合は削除しないでください -->
  <script type='text/javascript'>
  topics(1); //タブボックス用の関数の呼び出し
  </script>

  </div>";


	$body .="

	<div class='section normal'>";

  //検索窓
  $body .= index_search_windows($rireki_type,$perinfoclass,$searchclass,$usersess,$dispopt,$opt_ser_disp_lev,$opt_ser_hist_num,$keyword_org);

  //今日の日付をセット
  $today = date('Y/m/d');

//  $where_newinfo = body_newinfo_where();
  list($tcnt1,$tcnt2) = newinfo_check();

/*
  $sql = "select Count(*) AS cnt from tblinfo where $where_newinfo ";
  dp(sql,$sql);
  //SQL文を発行します
  $mysqli->query($sql);
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  $tcnt = $col['cnt'];
  dp(tcnt,$tcnt);

  mysqli_free_result($rst);
*/

	$body .="
  </div>
  
  <div class='section normal update'>
  <div class='heading'>
  <h2>新着情報</h2>
  </div>";
  
  $body .="<dl class='clearFix'>";

  if ( $tcnt1 > 0 or $tcnt2 > 0) {
    $body .="<dt>$today</dt>";
  }
  if ( $tcnt1 > 0 ) { 
    $body .="<dd>昨日からの更新情報が<b><a href='search.php' target='_blank'>" . $tcnt1 . "件</a></b>あります</dd>";
  }
  if ( $tcnt2 > 0 ) {
    $body .="<dd>過去1週間での更新情報が<b><a href='search.php' target='_blank'>" . $tcnt2 . "件</a></b>あります</dd>";
  }

  $body .="<dt>2018/04/18</dt><dd><b><font color='navy'><a href='urilink.php?infoid=1961' target='_blank' style='text-decoration:;'>戦略会議発表資料(4/12)</a></font></b>が登録されました。</dd>";
  $body .="<dt>2018/03/30</dt><dd><b><font color='navy'><a href='urilink.php?infoid=1914' target='_blank' style='text-decoration:;'>MIS2事業本部 2018年度事業方針</a></font></b>を登録しました。</dd>";
  $body .="<dt>2017/12/04</dt><dd><b><font color='navy'>各部内情報共有のための情報区分を追加</font></b>しました。</dd>";
  $body .="<dd>部内での情報共有に活用してください。</dd>";
  $body .="<dt>2017/11/20</dt><dd>最新の情報は、<b><font color='navy'>検索ボタンクリック</font></b>で表示されます。</dd>";
  $body .="<dd>当ページ下で<b><font color='navy'>ウォッチワード登録、検索</font></b>可能です。</dd>";

/*
  $body .="<dt>2017/10/23</dt><dd>部活動方針を登録しました。<a href='urilink.php?infoid=1403' target='_blank' style='text-decoration:;'>下期各部活動方針</a></dd>";
  $body .="<dt>2017/10/13</dt><dd>下期事業方針を登録しました。<a href='urilink.php?infoid=1358' target='_blank' style='text-decoration:;'>事業方針</a></dd>";
*/
  $body .="<dt>2017/10/02</dt><dd>事業部の情報ポータルを試用開始しました。<a href='site_mokuteki.php' target='_blank' style='text-decoration:;'>サイト概要説明</a></dd>
  </dl>
  </div>";

  //$body .="<dt>2017/10/16</dt><dd>西日本ＢＧ 2017年上期決算状況を登録しました。<a href='urilink.php?infoid=1375' target='_blank' style='text-decoration:;'>上期決算状況</a></dd>
 
	
	$body .="


	<div class='section normal'>

	<div class='heading'>
	<h2>技術相談</h2>
	</div>
	<p>IT本部技術メーリングリスト(ITtech)に相談してください。<p><a href='mailto:ittech-infra@ml.css.fujitsu.com'>ittech-infra@ml.css.fujitsu.com</a></p>
	</div>

	<div class='section normal'>

	<div class='heading'>

	<h2>情報ポータルについて</h2>

	</div>

	  <p>当ポータルは組織の情報共有効率化と個人の情報検索効率化を進めるサイトです。</P>
	 
	  <p><a href='file://v130.osss.g01.fujitsu.local/f02489/99_旧基盤ポータル/03_第一SI事業部/10_事業部共通/12_重点施策活動/ワークスタイル変革推進/情報共有基盤/事業部情報共有基盤説明.ppt'>詳細説明はこちら</a></p>

	</div>

	</div>
	<!-- メインカラム終了 -->";

  //幹部メニュー表示用のチェック
  $sql = "SELECT member_class FROM user where userid = \"$usersess\" ";
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  if ( $col['member_class'] == "2itssys-kanbu" ) {
    $kanbu_flg = "yes";
  }    
  mysqli_free_result($rst);

  //共通のカテゴリ情報を読み込む
  $sql = "SELECT * FROM tblcategory order by categoryid ";
  $rst = $mysqli->query($sql);

  $cnt = 1 ;
  while ($col = mysqli_fetch_array($rst)) { 
    $topmenu[$cnt] = $col['categoryname'];
    $cnt++;
  }
	$body .="

	<!-- サイドバー(A)開始 -->
	<div id='nav'>

	<div class='section subMenu'>

	<h2>情報カテゴリ</h2>

	<ul class='nl'>";
	
	for ($i = 1; $i < $cnt; $i++) {
	  if ( $topmenu[$i] == "幹部管理" ) {
	    if ( $kanbu_flg == "yes" ) {
	      $body .="<li align='left'><a href='search.php?searchclass[]=共通&keyword=$topmenu[$i]' target='_blank'>$topmenu[$i]</a></li>";
        }
      } else {
        $body .="<li align='left'><a href='search.php?searchclass[]=共通&keyword=$topmenu[$i]' target='_blank'>$topmenu[$i]</a></li>";
      }   
	}  


	$body .="
	</ul>

	</div>

	</div>
	<!-- サイドバー(A)終了 -->


	<hr class='clear'>

	</div>
	<!-- コンテンツ終了 -->


	<!-- サイドバー(B)開始 -->
	<div id='aside'>

	<div class='section strong'>

	<h2>重要広報</h2>

	<dl class='clearFix'>
    <!-- イメージをつけたい場合は以下 
	<dt><a href='#'><img src='noimage.jpg' alt='サンプル' width='90' height='60'></a></dt>
	<dd><a href='#'>重要で告知したい内容に対してここからリンク。</a></dd>
	-->
<!--
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/itkibanbg/_layouts/listform.aspx?PageType=4&ListId={2CB96FAB-7EAD-4A95-9E8D-615D6E83B208}&ID=241&ContentTypeID=0x010400C6E432C75C1007479CB9F577EA8EBF31' target='_blank'>【注意喚起】IPCOM重要障害</a><br>
-->
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='search.php?searchclass[]=共通&keyword=組織再編手続き関連' target='_blank' style='text-decoration:none;'>組織再編事務手続きについて</a><br>
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='infodisplay.php?infoid=1242' target='_blank' style='text-decoration:none;'>古田常務からのメッセージ</a><br>
<!--
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1917' target='_blank' style='text-decoration:none;'>山村本部長、清水本部長からのメッセージ<br>　(2月全体朝礼)</a><br>
-->


	</dl>

	</div>

	<div class='section pickup'>

	<h2>リンク</h2>
	<ul>

	<li><dl class='clearFix'>
	<!--
	<dt><a href='#'><img src='noimage.jpg' alt='サンプル' width='90' height='60'></a></dt>
	<dd><a href='#'>ピックアップコンテンツ1</a><br>ピックアップコンテンツに関する簡単な説明。</dd>
	-->
	<!-- <a>【良く見るサイト】</a><br> -->
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='cal0_2018.php' target='_blank' style='text-decoration:none;'>タスクダッシュボード</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='cal0_1IT.php' target='_blank' style='text-decoration:none;'>タスクダッシュボード(3クラ1シス)</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='cal0_road.php' target='_blank' style='text-decoration:none;'>情報共有改善施策一覧</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1453' target='_blank' style='text-decoration:none;'>旧SI事業部 OSSSフォルダ</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1915' target='_blank' style='text-decoration:none;'>マルイト10F 本部会議室予約</a><br>";


	if ( $kanbu_flg == "yes" ) {
  	$body .="<img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1321' target='_blank' style='text-decoration:none;'>MIS2幹部OSSSフォルダ</a><br>";
  }
	$body .="
    <!-- 2017.11.3 コメントアウト　あまり使っていないと想定
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='link_itp.php' target='_blank' style='text-decoration:none;'>ITポータルリンク集</a><br>
    -->
	</dl></li>


	<li><dl class='clearFix'>
	<!--
	<dt><a href='#'><img src='noimage.jpg' alt='サンプル' width='90' height='60'></a></dt>
	<dd><a href='#'>ピックアップコンテンツ2</a><br>ピックアップコンテンツに関する簡単な説明。</dd>
	-->
    <a>【各種規定・基準情報】</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1977' target='_blank' style='text-decoration:none;'>MIS2プロセス運用手引き(決裁基準)</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=2052' target='_blank' style='text-decoration:none;'>4クラ事運用プロセス規定</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1488' target='_blank' style='text-decoration:none;'>エスカレーション基準</a><br>



	</dl></li>
	<li class='last'><dl class='clearFix'>
	<!-- ↑最後の項目（li要素）にクラス名（last）を設定してください -->
	<!--
	<dt><a href='#'><img src='noimage.jpg' alt='サンプル' width='90' height='60'></a></dt>
	<dd><a href='#'>ピックアップコンテンツ3</a><br>ピックアップコンテンツに関する簡単な説明。</dd>
	-->
    <a>【おすすめリンク】</a><br> 
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='http://seshien.fwest.css.fujitsu.com/knowledge/open.knowledge/list' target='_blank' style='text-decoration:none;'>Knowledge（SE作業効率化）<br>　(旧SE支援ツールログオン要)</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=384' target='_blank' style='text-decoration:none;'>旧SE支援ツール</a><br>
<!--    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='info_ranking.php' target='_blank' style='text-decoration:none;'>情報ポータル内アクセスランキング</a><br> -->
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='search.php?dispclass=&keyword=&orderby_opt=click' target='_blank' style='text-decoration:none;'>サイト内閲覧数ランキング</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='search.php?dispclass=&keyword=&orderby_opt=valuep' target='_blank' style='text-decoration:none;'>サイト内評価数ランキング</a><br>




	</dl></li>

	</ul>

	</div>

	<div class='section emphasis'>

	<h2>メッセージ</h2>
	<ul>
<!--
	<li ><a href='urilink.php?infoid=1455' target='_blank' style='text-decoration:none;'>柴田本部長代理メッセージ</a></li>
-->
	<li ><a href='urilink.php?infoid=2055' target='_blank' style='text-decoration:none;'>2018年度事業部方針</a></li>
<!--
	<li ><a href='urilink.php?infoid=1916' target='_blank' style='text-decoration:none;'>事業部長メッセージ</a></li>
-->
	<li ><a href='# 'target='_blank' style='text-decoration:none;'>部長メッセージ</a></li>
	</ul>

	<div class='section normal'>

	<h2>エスカレーション</h2>

	<p><a href='urilink.php?infoid=1488' target='_blank' style='text-decoration:none;'>問題が起こったら、迷わず上司にエスカレーションしてください。</a></p>

	</div>


	<div class='section normal'>

	<h2>SEワークスタイル変革</h2>

	<p><img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='#'  align='left' style='text-decoration:none;'>仕事の進め方</a></p>
	<p><img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='#'  align='left' style='text-decoration:none;'>仕事改善提案</a></p>

	</div>

<!--
	<div class='section normal contact'>
	<h2>技術相談</h2>
	<p>気軽にメール</p>
	<p class='tel'>ittech-infra@ml.css.fujitsu.com</p>
	<p class='form'><a href='mailto:ittech-infra@ml.css.fujitsu.com'>気軽に相談</a></p>
	</div>
-->

	<div class='section normal'>

	<h2>改善要望</h2>

	<p><a href='infonewyobo.php'>ポータルに関しての改善、新規要望に対してはここからリンク。</a></p>
<!--	<p><a href='#' style='text-decoration:none;'>ポータルに関しての改善、新規要望に対してはここからリンク。</a></p>-->

	</div>


	<hr class='none'>

	</div>

	<!-- サイドバー(B)終了 -->

	</div>
";

	return $body;

	}

function body_top_footer() {

$body .="	<!-- フッタ開始 -->
	<div id='footer'>

	<ul class='nl'>
	<li class='first'><a href='index.php'>ホーム</a></li>
	<li><a href='site_mokuteki.php' target='_blank'>サイト目的</a></li>
<!--	<li><a href='index.php'>運営スタッフ</a></li>  -->
	<li><a href='infonewyobo.php'>改善／要望／質問</a></li>
<!--	<li><a href='index.php'>案内</a></li>  -->
	</ul>

	<ul class='nl guide'>
	<li class='first'><a href='info_faq.php'>FAQ</a></li>
<!--	<li><a href='index.php'>プライバシーポリシー</a></li> -->
	<li><a href='infostamap.php'>サイトマップ</a></li>
<!--	<li><a href='index.php'>ご意見</a></li> -->
	</ul>

	<address>
	GSI部門 グローバルデリバリーG  第ニＭＩＳ事業本部  第四クラウドインテグレーション事業部
	<br>
	</address>


	</div>

	</div>

	";

	return $body;
	//return array($imagefile,$errmsg);  

	}
//トップの検索窓用
function index_search_windows($rireki_type,$perinfoclass,$searchclass,$usersess,$dispopt,$opt_ser_disp_lev,$opt_ser_hist_num,$keyword_org) {

  $body .= "<table><form name='kensaku' action='search.php' method='get'>";

  //検索窓用のHTML本文
  $body .=  "ポータル内検索　<input size='48' name='keyword' type='text' id='keyword' value=\"$keyword_org\">&nbsp;<input id = 'button_m' value='　検索　' type='submit' ><A title='and検索:スペース&#13;&#10;or検索：「、」で可能です。'><img src='question16.png'></a>";
  $body .= "<style type='text/css'>
  input.withicon {
    background-image: url('search_16.png');
    background-repeat: no-repeat;
    background-position: 2px center;
    padding-left: 21px;
  }
  </style>";

  $body .= "</form></table>
          <SCRIPT language='JavaScript'>
             document.getElementById('keyword').focus();
          </SCRIPT>";

  return $body;
}



function htmlfooter() {
	//各ページのフッタ部のHTMLを組み立てる

	/*
	  $body = "</body></html>";
	*/

	  $body = "
	  <div id='hpb-footer'>
	    <div id='hpb-footerMain'>
	      <p>Information Portal&nbsp;2017.</p>
	    </div>
	  </div>
	</body>
	</html>";
  return $body;
}

?>
