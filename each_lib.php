<?php
/****************************************************/
/*  include file for jportal                        */
/****************************************************/

  header('Content-Type: text/html; charset=Shift_JIS');


  $DBSERVER   = "sddb0040058795.cgidb";    //MySQL�T�[�o�[��
  $DBUSER     = "sd_dba_NjkwNTU5";         //���O�C�����[�U�[��
  $DBPASSWORD = "ryomiyo#2008";             //�p�X���[�h
  $DBNAME     = "sddb0040058795";   //�f�[�^�x�[�X��

  $PICTUREDIR   = "picture/";       //�ʐ^�t�@�C���̍ŏI�ۑ���p�X
  $PICTURETMP   = "picturetmp/";    //�ʐ^�t�@�C���̈ꎞ�ۑ���p�X

  $FILEDATADIR   = "filedata/";       //�t�@�C���̍ŏI�ۑ���p�X
  $FILEDATATMP   = "filedatatmp/";    //�t�@�C���̈ꎞ�ۑ���p�X

  $ADMSESS    = "sslogined";    //�Ǘ��҃��O�C���Ŏg���Z�b�V�����ϐ���
  $USERSESS    = "sslogined";    //���O�C���Ŏg���Z�b�V�����ϐ���

function newinfo_check() {

  $usersess = $_SESSION['USERSESS'];

  $upddate_from_a = date("Y-m-d",strtotime('-1 day')); //�ߋ����̎w��
  $upddate_from_b = date("Y-m-d",strtotime('-7 day')); //�ߋ����̎w��
  $upddate_to   = date("Y-m-d");                     //�����̐ݒ�

  //���O�\���p�^�[��
  $mysqli = my_sql_connect("sjis");

  //�l�̌����\���敪���擾
  $searchclass = perinfoclass_set($usersess);

  //�o�^���ʌ����̏ꍇ�́A�A�N�Z�X�ł���S�Ă�\��
  $infoclass = ALL; 

  //�o�^���ʌ�����WHERE����쐬(�J�n)
  $where = " ((infoavaflg != '�폜') and ";

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
    if ( $searchclass[$i] == "�l" ) {
      $where .= " (infoclass = \"$searchclass[$i]\" and regid = \"$usersess\") ";
      $keynaviso .= $searchclass[$i] ;
    }
    else {  
      $where .= " (infoclass = \"$searchclass[$i]\" and class_id = \"$class_id_v\"  ) ";
    }
  }

  $where .= ") and ";
  $where_a .= " (upddate BETWEEN \"$upddate_from_a\" AND \"$upddate_to\" )) "; //���Ԏw��̏����ݒ�
  $where_b .= " (upddate BETWEEN \"$upddate_from_b\" AND \"$upddate_to\" )) "; //���Ԏw��̏����ݒ�

  $sql = "select Count(*) AS cnt from tblinfo where $where" . $where_a;
  //SQL���𔭍s���܂�
  $mysqli->query($sql);
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  $tcnt_a = $col['cnt'];

  $sql = "select Count(*) AS cnt from tblinfo where $where" . $where_b;
  //SQL���𔭍s���܂�
  $mysqli->query($sql);
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  $tcnt_b = $col['cnt'];

  mysqli_free_result($rst);

  //�o�^���ʌ�����WHERE����쐬(�I��)
  return array($tcnt_a,$tcnt_b);

}


//�w�肵��DB�ł̃L�[���[�h�������ʂ�body��Ԃ��֐�
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

  //���O�������݃t���O�@�f�t�H���g off:�������݂Ȃ�
  $log_write_flg = "off"; // on:��������
  
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

  //classid�̎󂯎���ǉ�
  $classid = $get_classid;
  $categid = $get_categid;

  $rireki_type = $get_rireki_type;
  //�N���b�N����\���p�ϐ�
  $click_rireki = $get_click_rireki;

  //�l���̌������\���敪�̃Z�b�g
  $perinfoclass = perinfoclass_set2($db_con_info,$usersess);

  //�T�[�`�̑I���敪�̃Z�L�����e�B�`�F�b�N�i�S�Ă̋敪���o�^����Ă��邩�ƍ��`�F�b�N�j
  $searchclass_chk_f = "OK"; 
  $searchclass_chk_cnt= 0;
  for ( $i=0 ; $i < sizeof($get_searchclass);$i++) { 
    for ( $j=0 ; $j < sizeof( $perinfoclass );$j++) {
      if ( $get_searchclass[$i] == $perinfoclass[$j] ) {  
        $searchclass_chk_cnt = $searchclass_chk_cnt + 1;
      }  
    }
  }
  
  if ( $searchclass_chk_cnt != sizeof( $get_searchclass ) ) { //�w�肳�ꂽ�敪���S�ĂȂ��ꍇNG
    $searchclass_chk_f = "NG";
    $get_searchclass = $perinfoclass; //NG�̏ꍇ�́A�l�̐ݒ�������Z�b�g
  } else { //�w�肵���敪���������ꍇ�Aget�����p�p�����[�^body�쐬
    $infoclass_body ="";
    for ( $i=0 ; $i < $searchclass_chk_cnt ; $i++) { 
      $infoclass_body .= "searchclass[" . $i . "]=" . $get_searchclass[$i] ; //�T�[�`�敪�̐ݒ�
      $last_chk = $i + 1; 
      if ( $last_chk < $searchclass_chk_cnt) { //���X�g�łȂ����&��ǉ�
        $infoclass_body .= "&";
      }
    }
  }    

  //�T�[�`�̑I���敪�̃Z�b�V�����Ԉ��p��
  //�w�肳�ꂽ�����敪����DB�ł��邩�`�F�b�N
  //���v���Ă��鐔���`�F�b�N

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
    //�l�̌������敪�����ׂĐݒ�
    $searchclass = hold_searchclass($perinfoclass,$searchclass, $perinfoclass);
  }

  //DB�ɐڑ�
  $mysqli = my_sql_connect_any($db_con_info,"sjis") ;

  //�l�̓o�^�I�v�V������ǂݍ���
  $sql = "SELECT * FROM user where userid = \"$usersess\" ";
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);

  $opt_ser_disp_menu = $col[opt_ser_disp_menu];//�������j���[�̎��
  $opt_ser_disp_lev = $col[opt_ser_disp_lev];//�����\���̃��x��
  $opt_ser_disp_num = $col[opt_ser_disp_num];//�������ʂ̕\������
  $opt_ser_hist_num = $col[opt_ser_hist_num];//�L�[���[�h��������

  //�l�̃E�H�b�`���[�h�L���m�F
  if ( $search_opt == "watch") {
    $keyword     = $col['watchword'];//�L�[���[�h
    $keyword_org = $col['watchword'];//�L�[���[�h
  }  
  //dp(keyword_org,$keyword_org);

  //���O�p�ݒ�
//  $dispname = "search.php";
  $scene = "�L�[���[�h�T�[�`";

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
  //�E�H�b�`���[�h�\���̏ꍇ�́A���̃��[�h���擾
  if ($dispopt == "watch" ){ 
    $sql = "SELECT * FROM user WHERE userid = \"$usersess\" " ;

    //���ʃZ�b�g���擾���܂�
    $rst = $mysqli->query($sql);
    $col = mysqli_fetch_array($rst);

    $keyword_org = $col['watchword'];
    $keyword = $col['watchword'];

    if ( strlen($keyword) != 0 ) {
      $searchtype = "kw";
    }
    //���O�p�ݒ�
    $dispname = "watchword";
    $scene = "�E�H�b�`���[�h";
  }
*/

  //�P�y�[�W����̕\��������ݒ�
  $PAGESIZE = $opt_ser_disp_num;

  //�{���쐬
  if ( $search_opt == "watch" and $keyword !="" ) {
    $body_result_title = "<h3>�E�H�b�`���[�h���</h3><a href='userupd.php#watchword' align='right' target='_blank' style='text-decoration:none;'>�E�H�b�`���[�h�o�^�͂�����</a>" ;
  } else {
    $body_result_title = "<h3>�ŐV�o�^���</h3><a href='userupd.php#watchword' align='right' target='_blank' style='text-decoration:none;'>�E�H�b�`���[�h�o�^�͂�����</a></p>" ;
  }
  
  $body .= "<div id='hpb-wrapper'><div id='hbp-main'>" . $body_result_title . ""; 
//  $body .= "<div id='hpb-inner'><div id='hpb-wrapper'><div id='hbp-main'>" . $body_result_title . "";  �����N�\���ł��Ȃ�
//  $body .= "<div id='hpb-main'><h3>�y�@" . $host_name . "�@�z</h3>";

  //���敪��I������HTML�𐶐�
  list($body_tmp,$search_class_chk) = body_search_class_any($host_url,$rireki_type,$perinfoclass,$searchclass);
  $infodiv_body = $body_tmp;
  
  //�����\���̑��̕�����HTML�𐶐�
  $searchwindow = body_search_windows_any($db_con_info,$host_url,$host_name,$usersess,$dispopt,$opt_ser_disp_menu,$opt_ser_hist_num,$keyword_org);

  //�����̎�ނ𔻕�
  //  if (isset($keyword) and strlen($keyword) >= 0 and strlen($categoryname) == 0 ) {
    
  //�N���b�N��������
  if (isset($get_click_rireki)) {

    $searchtype = "cr";//�����^�C�v���N���b�N���������ɐݒ�
    $resultmes = "�@�N���b�N������";

  } else {
  //�L�[���[�h����
    $searchtype = "kw";//�����^�C�v���L�[���[�h�����ɐݒ�

    //�s���v���̏ꍇ�̓��b�Z�[�W���o���A���O�o��
    if ( $searchclass_chk_f == "NG") {
      print htmlheader("�����G���[");
      print "�s���ȃA�N�Z�X�ł��B���敪�͌l�ŕ\������Ă�����̈ȊO�͎w��ł��܂���B";
      $result = "�s���A�N�Z�X";
      $comment = "�s���ȏ��N���X�ł̃A�N�Z�X������܂����B���敪: ";
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

    //�������ʂ̃��b�Z�[�W�쐬
    $resultmes = "���敪�F"; 
    if (strlen($keyword) == 0 ) {
      if ( isset($searchclass[0]) ) { //�J�e�S�������������L�[���[�h�Ȃ��̏ꍇ
        $kw_opt = "ALL";
        for ($i = 0; $i < sizeof($searchclass); $i++) {
          $resultmes .= "�u" . $searchclass[$i] . "�v"; 
        }
        $resultmes .= "��";
        $result = "�����\��";
      } else {
        $resultmes .= "�@�S����";
        $result = "�S���\��";
      }
      $result = "�S���\��";
      if ( $log_write_flg == "on" ) {
        logw($userid,$dispname,$scene,$searchtype,$result,$tcnt,$keyword_org,$categid,$regdate,$comment);
      }
      
    } else { 
      //�L�[���[�h��\������body�𐶐�
      $kw_opt = "";
      for ($i = 0; $i < sizeof($searchclass); $i++) {
        $resultmes .= "�u" . $searchclass[$i] . "�v"; 
      }
      $resultmes .= "�@�L�[���[�h�F�u" . $keyword_org . "�v��";
    }


  }
  //dp(searchtype,$searchtype);

  //�����̎�ނɉ�����WHERE������g�ݗ���
  switch ($searchtype) {

    //�L�[���[�h������WHERE
    case "kw":

      $research_opt = "infoclass=" . $infoclass . "&dispclass=". $dispclass . "&keyword=". $keyword;

      //�L�[���[�h�󔒂̏ꍇ�A�S���\��
      if ( $kw_opt == "ALL" ) { 
        $where = " WHERE ((infoavaflg != '�폜') and ";

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
          
          if ( $searchclass[$i] == "�l" ) {
            $where .= " (infoclass = \"$searchclass[$i]\" and regid = \"$usersess\") ";
            $keynaviso .= $searchclass[$i] ;
          }
          else {  
            $where .= " (infoclass = \"$searchclass[$i]\" and class_id = \"$class_id_v\") ";
            $keynaviso .= $searchclass[$i] ;
          }   
        }
        $where .= ") )";

      } else { //�L�[���[�h���󔒂łȂ���΃L�[���[�h��z��Ɋi�[���� 

        //�L�[���[�h����G�X�P�[�v��������菜��
        //���������Ή��̂��߃R�����g�A�E�g
        //$keyword = stripcslashes($keyword);

        //�L�[���[�h�̑O��̃X�y�[�X����菜��
        $keyword = trim($keyword);
      
        //�S�p�X�y�[�X�̔��p�ϊ��Ɣ��p�J�i�̑S�p�ϊ�
        $keyword = mb_convert_kana($keyword, "sKV", "SJIS");

        //���p�����̑S�p�������A�S�p�����̔��p�������������ΏۂƂ��Ēǉ�(2017.9.9�ǉ��j
        $keyword_h = mb_convert_kana($keyword, "a", "SJIS");
        $keyword_z = mb_convert_kana($keyword, "A", "SJIS");
        
        //�L�[���[�h���J���}���X�y�[�X�ŕ������Ĕz��ɑ��
        if(!strrchr($keyword, " ")){

          //�L�[���[�h�ɔ��p�X�y�[�X���܂܂�Ă��Ȃ��ꍇ
          $keyword = str_replace("�A", ",", $keyword);
          $keyword_h = str_replace("�A", ",", $keyword_h);
          $keyword_z = str_replace("�A", ",", $keyword_z);

          $keyword = str_replace("�C", ",", $keyword);
          $keyword_h = str_replace("�C", ",", $keyword_h);
          $keyword_z = str_replace("�C", ",", $keyword_z);

          $arykey = explode(",", $keyword);
          $arykey_h = explode(",", $keyword_h);
          $arykey_z = explode(",", $keyword_z);

          $tmpkey = "Or";

        } else {

          //�L�[���[�h�ɔ��p�X�y�[�X���܂܂�Ă���ꍇ
          $arykey = explode(" ", $keyword);
          $arykey_h = explode(" ", $keyword_h);
          $arykey_z = explode(" ", $keyword_z);

          $tmpkey = "and";

        }

        //�������ꂽ�e�L�[���[�h����łȂ����`�F�b�N
        for ($i = 0; $i < sizeof($arykey); $i++) {
          if (strlen($arykey[$i]) == 0) {
            //�������ꂽ�L�[���[�h�̂����ꂩ����̏ꍇ
            $body .= "�L�[���[�h�̎w�肪����������܂���I
                  <INPUT id='button_m' type='button' value='�����֖߂�'
                  onclick='window.location=\"searchmenu.php\"'>";
            print htmlheader("��������") .  $body . $infodiv_body . $searchwindow . htmlfooter();
            exit();
          }
        }

        //�L�[���[�h������WHERE����쐬(�J�n)
        $where = " WHERE ((infoavaflg != '�폜') and ";

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
          if ( $searchclass[$i] == "�l" ) {
            $where .= " (infoclass = \"$searchclass[$i]\" and regid = \"$usersess\") ";
            $keynaviso .= $searchclass[$i] ;
          }
          else {  
            $where .= " (infoclass = \"$searchclass[$i]\" and class_id = \"$class_id_v\" ) ";
          }
        }

        $where .= ") and (";
          
        $where .= "( "; //�L�[���[�h�̌����pwhere�吶��

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
 
        $where .= ") or ( ";  //���p�ϊ������L�[���[�h�ł̌����pwhere�吶��

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

        $where .= ") or ( "; //�S�p�ϊ������L�[���[�h�ł̌����pwhere�吶��

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
          //�L�[���[�h������WHERE����쐬(�I��)
        }
 
      break;

    //�N���b�N����������WHERE
    case "cr":

      $research_opt = "&click_rireki=" . $click_rireki ;

      //����\������
      $rc = 5;
      
      $sql = "SELECT * FROM tblsearchlog WHERE ( userid = '$userid') and  ( scene = '�����N�N���b�N') ORDER BY serno  desc LIMIT 0,$rc";
      $sqlcnt = "SELECT Count(*) AS cnt from tblsearchlog WHERE ( userid = '$userid') and ( scene = '�����N�N���b�N')";
       
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
  
  //���я��iORDER BY�j�̐���
  if (!isset($sorttype) or $sorttype == 1) {
    //�͂��߂ČĂ΂ꂽ�Ƃ��܂��̓J�e�S�����w��̂Ƃ�
    //�o�^���̍ŐV���w��ɕύX ���Ԃ�ǉ�
    $orderby = " ORDER BY upddate desc,updtime desc, tblinfo.categoryid, infoid";
  }
  else {
    //�o�^�����Ŏw��i�f�t�H���g�j
    //���Ԃ�ǉ�
    $orderby = " ORDER BY upddate desc,updtime desc, infoid";
  }
  if ( $click_rireki == "cr" ) {
    //�w��Ȃ��̏ꍇ serno ���\�[�g���Ɏw��
    $orderby = " ORDER BY serno desc ";
  }

  
  //���я��̍Č������f
  if ( $orderby_opt == click and $click_rireki == "cr" ) {
    $orderby = " ORDER BY clickcnt desc,infovaluep desc,upddate desc,updtime desc, tblinfo.categoryid, infoid , tblsearchlog.serno desc "; //�����N���b�N���Ȃ�]�������� �ɉ����ĐV������
  } else if ( $orderby_opt == click ) { 
    $orderby = " ORDER BY clickcnt desc,infovaluep desc,upddate desc,updtime desc, tblinfo.categoryid, infoid"; //�����N���b�N���Ȃ�]��������
  }

  //���я��̍Č������f
  if ( $orderby_opt == valuep ) {
    //�����]���|�C���g�Ȃ�N���b�N������
    $orderby = " ORDER BY infovaluep desc,clickcnt desc,upddate desc,updtime desc, tblinfo.categoryid, infoid";  }

  if (!isset($page)) {

    //���߂ČĂ΂ꂽ�Ƃ��͑��������擾
    $sql = "SELECT Count(*) AS cnt FROM tblinfo" . $where;

    if ( $searchtype != "cr" ) {
      $rst = $mysqli->query($sql);
      $col = mysqli_fetch_array($rst);
      $tcnt = $col['cnt'];
      //dp(tcnt2,$tcnt);
      mysqli_free_result($rst);
    } else {
      //�����^�C�v�̏ꍇ�́A�J�E���g��SQL��ύX
      $sqlcnt = "SELECT Count(*) AS cnt from tblsearchlog WHERE (userid = '$userid') and ( scene = '�����N�N���b�N')";
      $rstcnt = $mysqli->query($sqlcnt);
      $colcnt = mysqli_fetch_array($rstcnt);
      $tcnt = $colcnt['cnt'];
      //dp(tcnt3,$tcnt);
      mysqli_free_result($rstcnt);
    }

    //�Y���������`�F�b�N
    if ($tcnt == 0) {

      //���O��������
      $result = "�����L�[���[�h�Ȃ�";
      if ( $log_write_flg == "on" ) {
        logw($userid,$dispname,$scene,$searchtype,$result,$tcnt,$keyword_org,$categid,$regdate,$comment);
      }
      //http,https,file�ł���ΐV�K�o�^���j���[��
      if ( substr($keyword_org,0,5) == "http:" or substr($keyword_org,0,6) == "https:" or substr($keyword_org,0,5) == "file:") {

        //�Ώۃy�[�W�̃^�C�g��������΂����infotitle�ɓ���Ainfokind��web�ŏ����ݒ�
        if ( getPageTitle( $keyword_org ) != "" ) {
          $infotitle = curl_get_contents( $keyword_org,60 );
          $infokind = "web";
        }

        //�Ώۃy�[�W�̃��^�^�O������΂����infotag�ɓ��ꏉ���ݒ�
        $array_tag = get_meta_tags( $keyword_org );
        if ( $array_tag['keywords'] != ""  ) {
          $infotag = htmlspecialchars($array_tag['keywords'], ENT_COMPAT);
          $infotag = mb_convert_encoding($infotag,'Shift_JIS','auto');
        }
        if ( $array_tag['description'] != ""  ) {
          $comment = htmlspecialchars($array_tag['description'], ENT_COMPAT);
          $comment = mb_convert_encoding($comment,'Shift_JIS','auto');
        }
        $body .= "<br>���������Ɉ�v������͂���܂���ł����B�@
                 <INPUT id='button_m' type='button' value='�߂�' 
                 onclick='history.back()'>�@�@�@<A href='infonew.php?urlinfo=$keyword_org&infotitle=$infotitle&infokind=$infokind&infotag=$infotag&comment=$comment'>�������N����V�K�o�^</a><br>" . $infodiv_body. $searchwindow ;

        $body .= "<meta http-equiv='refresh' content='0; url=infonew.php?urlinfo=$keyword_org&infotitle=$infotitle&infokind=$infokind&infotag=$infotag&comment=$comment'>";
        
      } else {
        $body .= "<br>���������Ɉ�v������͂���܂���ł����B�@
                 <INPUT id='button_m' type='button' value='�߂�' 
                 onclick='history.back()'>";
        $infotitle = "�L�[���[�h�u" .  $keyword_org . "�v �����������ǂ���܂���ł���";
        $infokind = "������";
        $infoclass = "����";
        $categoryid = 33;
        $comment = " ����T���Ă������ȒP�ɋ����Ă��������B���P�ƌ����̎x�������܂��B" ; 
        $body .= "�@�@�@<A href='infonew.php?infotitle=$infotitle&infokind=$infokind&infotag=$infotag&comment=$comment&?categoryid=$categoryid'>������܂���ł����I�o�^</a><br>";
        $body .= $infodiv_body . $searchwindow ;

      } 

      $search_result = "nothing";
    }
    //���݃y�[�W�������ݒ�
    $page = 1;
  }

  if ($search_result != "nothing") {
  //���y�[�W�����v�Z
  $totalpage = ceil($tcnt / $PAGESIZE);

  //�y�[�W�㕔�̕\����g�ݗ���
  $body .= "<SCRIPT language='JavaScript'><!--
            function EditExec(infoid) {
              document.mainfrm_" . $host_name . ".action = 'infoupd.php';
              document.mainfrm_" . $host_name . ".infoid.value = infoid;
              document.mainfrm_" . $host_name . ".submit();
            }
            function DeleteCheck(infoid) {
              if(confirm('�{���ɍ폜���Ă����ł����H')){
                document.mainfrm_" . $host_name . ".action = 'infoupdexec.php';
                document.mainfrm_" . $host_name . ".infoid.value = infoid;
                document.mainfrm_" . $host_name . ".proc.value = 'del';
                document.mainfrm_" . $host_name . ".submit();
              }
            }
            // --></SCRIPT>";

 
//  $body .= $resultmes . "�@$tcnt ���̏�񂪓o�^����Ă��܂��B ";
  $body .= "<p align='left'>" . $resultmes . "�@$tcnt ���̏�񂪓o�^����Ă��܂��B ";
  $body .= "[" . ($PAGESIZE * ($page - 1) + 1) . "-";

  if ($page < $totalpage) {
    //�ŏI�y�[�W���O�̃y�[�W�̂Ƃ�
    $body .= ($PAGESIZE * $page) . "] ��\���B<br>";
  }
  else {
    //�ŏI�y�[�W�̂Ƃ�
    $body .= "$tcnt] ��\���B<br>";
  }
  $body .= "</p>";

  //�P�y�[�W���������o����SQL����g�ݗ���
  if ( $click_rireki == "cr" ) {
    //�N���b�N����\���̏ꍇ
    //����������log�e�[�u���̈ꕔ������select���Acomment�̋�����������
    $sql = "SELECT tblinfo.*,tblsearchlog.serno,accdate,acctime
          FROM tblinfo inner join tblsearchlog
          on tblinfo.infoid=tblsearchlog.infoid where ( userid = '$userid') and  ( scene = '�����N�N���b�N')" .
          $orderby .
          " LIMIT " . $PAGESIZE * ($page - 1) . ", $PAGESIZE";
  }
  else { 
  //�V�o�[�W�����Btblinfo��categoryinfo�����̂܂ܗ��p�B
  $sql = "SELECT * FROM tblinfo " . 
          $where . $orderby .
          " LIMIT " . $PAGESIZE * ($page - 1) . ", $PAGESIZE";
  }
  //dp(sql,$sql);

  //���ʃZ�b�g���擾
  $rst = $mysqli->query($sql);

  //�y�[�W�{����g�ݗ��Ă܂�

  //��������ǉ�
  //$body .= $infodiv_body;
  //$body .= $searchwindow;

  $body .= "<FORM name='mainfrm_" . $host_name . "' method='POST'>";

  //�y�[�W�̃i�r�Q�[�V������ǉ� (���ʕ��j

  //�y�[�W�i�r�Q�[�V�����̃p�����[�^��ݒ�
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

  // �㕔�i�r�Q�[�V�����p��HTML�쐬
  $body .= "<p align='left'>";
  if ($page > 1) {
    //2�y�[�W�ȍ~�̏ꍇ��[�擪]��[�O]��\��
    $body_navi_1 = "<A href = '$PHP_SELF?page=1&tcnt=$tcnt$keynavi'>&lt;&lt;
                    �擪��</A>&nbsp;&nbsp;&nbsp;
                    <A href = '$PHP_SELF?page=" . ($page - 1) . "&tcnt=$tcnt$keynavi'>&lt;
                    �O�� $PAGESIZE ��</A>&nbsp;&nbsp;&nbsp;
                    ";
    $body .= $body_navi_1;
  }
  if ($totalpage > 1 and $page < $totalpage) {
    //�S����2�y�[�W�ȏ゠���Ă����݂��ŏI�y�[�W���
    //�O�̂Ƃ���[��]��[�Ō�]��\��
    $body_navi_2 = "<A href = '$PHP_SELF?page=" . ($page + 1) . "&tcnt=$tcnt$keynavi'>
                    ���� $PAGESIZE ��&gt;</A>&nbsp;&nbsp;&nbsp;
                    <A href = '$PHP_SELF?page=$totalpage&tcnt=$tcnt$keynavi'>
                    �Ō��&gt;&gt;</A>
                    ";
    $body .= $body_navi_2;
  }
  $body .= "</p>";

  if (!isset($showtype) or $showtype == 1) {

    //�w�b�_�̍쐬
    $body .= "<TABLE><TR>";
    $body .= "<TH>���敪�^<br>�J�e�S��</TH>
              <TH>���e</TH>
              <TH>�����N</TH>";

    //�\���̃p�^�[���𔻒f
    if ($orderby_opt == click ) {
      $body .= "<TH><A href=search.php?$research_opt title='�X�V���̍ŐV���ŕ\��' >�X�V����<br>
                <A href=search.php?$research_opt&orderby_opt=click title='�{�����̑������ŕ\��' >���{������<br>
                <A href=search.php?$research_opt&orderby_opt=valuep title='�]���̑������ŕ\��' >�]������
                </TH>";
    } else if ($orderby_opt == valuep ) {
      $body .= "<TH><A href=search.php?$research_opt title='�X�V���̍ŐV���ŕ\��' >�X�V����<br>
                <A href=search.php?$research_opt&orderby_opt=click title='�{�����̑������ŕ\��' >�{������<br>
                <A href=search.php?$research_opt&orderby_opt=valuep title='�]���̑������ŕ\��' >���]������
                </TH>";
    } else {
      $body .= "<TH><A href=search.php?$research_opt title='�X�V���̍ŐV���ŕ\��' >���X�V����<br>
                <A href=search.php?$research_opt&orderby_opt=click title='�{�����̑������ŕ\��' >�{������<br>
                <A href=search.php?$research_opt&orderby_opt=valuep title='�]���̑������ŕ\��' >�]������
                </TH>";
    }  

    $body .= "<TH>ACT</TH></TR>";


    //�]���������ꂽ�ꍇ�̍Č����������Z�b�g
    $infovaluep_research_opt = $research_opt . "&orderby_opt=valuep";

    //���ʃZ�b�g����f�[�^�����[�v�œǂݍ���
    while($col = mysqli_fetch_array($rst)) {
      //�e���R�[�h���e��\������\��g�ݗ���

      //2017.9.9�ǋL �}�b�v���̃J�e�S�������ŉ����o�^����Ă��Ȃ��ꍇ�́A�\���ΏۊO�Ƃ��� 
      if (( $col['map_flg'] != 1 or ( $col['map_flg'] == 1 and ( $col['infotitle'] != "" or $col['uriinfo'] != "" ))) or $DBNAME != "jportal")  { 

        //�L�[���[�h���w�肳��Ă���Ƃ��̓J�e�S���̃L�[���[�h�𑾎��ɒu��
        $tmpcategoryname = $col['categoryinfo'];
        if ($searchtype == "kw") {
          for ($i = 0; $i < sizeof($arykey); $i++) {
            $tmpcategoryname = ereg_replace(preg_quote($arykey[$i]), "<B>". "\\0" . "</B>", $tmpcategoryname);
          }
        }
 
        //�^�C�g���A��ʁA�^�O�������e�{�����쐬
        $tmpcomment = "";
        if (strlen($col['infotitle']) > 0 ) {
          $tmpcomment .= $col['infotitle'] . "<br><br>";
        }

        //url��/�ϊ�
        $uriinfo = str_replace('�_','/', $uriinfo);
 
        //\\�A\�̕ϊ�����  \\�̃����N�̏ꍇ\��ϊ�
        if ( substr($urlinfo,0,1) == "\\" ) {
          $urlinfo =  str_replace("\\\\","file://", $urlinfo); 
          $urlinfo =  str_replace("\\","/", $urlinfo);
        } else if ( substr($urlinfo,0,2) == "C:" or substr($urlinfo,0,2) == "D:" ) {
          $urlinfo =  str_replace("\\","/", $urlinfo);
          $urlinfo =  "file:///" .  $urlinfo; 
        }  

        //���̃A�C�R���\���F�J�e�S���ɕ\��
        if ((strlen($col['infokind']) > 0) and ($col['infokind'] != '������' )) {
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

        //���̃A�C�R���\���F�敪�\������ꍇ�B���ʋ敪�ȊO�̓��b�N�}�[�N�\���B
        if ($col['infoclass'] != "����" ) {
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

        // �C���[�W������΃A�C�R����ǉ����A�N���b�N���ĕ\���\
        if ($col[infofilename] != "noimage.jpg" ) {
          $tmpcategoryname .= "<br><A href='$PICTUREDIR$col[infofilename]' target='_blank'>
                        <IMG src='image/picture.png'>";
        }

        //�R�����g��HTML����
        $tmpcomment .=  $col['comment'] ;

        if (strlen($col['infotag']) > 0 ) {
          $tmpcomment .= "<br><br>�^�O�F" .  $col['infotag'];
        } 

        //�L�[���[�h���w�肳��Ă���Ƃ��͏����e�̃L�[���[�h�𑾎��ɒu��
        if ($searchtype == "kw") {
          for ($i = 0; $i < sizeof($arykey); $i++) {
            $tmpcomment = ereg_replace(preg_quote($arykey[$i]), "<B>". "\\0" . "</B>", $tmpcomment);
          }
        }

        //�t�@�C���f�[�^������΂��̃����N��ǉ�
        if (strlen($col['filedataname_int']) <> 0 ) {
          $filedataurl = $host_url . "/" . $FILEDATADIR . $col['filedataname_int'];
          $tmpcomment .= "<br>" .  "<A href='filedataurl' target='_blank' >" . $col['filedataname'] . "</A>";
        } 

        //�L�[���[�h���w�肳��Ă���Ƃ���URL�̃L�[���[�h�𑾎��ɒu��
        $tmpuriinfo = $col['uriinfo'];
        if ($searchtype == "kw") {
          for ($i = 0; $i < sizeof($arykey); $i++) {
            $tmptmpuriinfo = ereg_replace(preg_quote($arykey[$i]), "<B>". "\\0" . "</B>", $tmpuriinfo);
          }
        }

        // ���敪�A�J�e�S������\��
        $body .= "<TR>";
        $body .= "<TD width='100' align='left' valign='top' >$col[infoclass]/<br>$tmpcategoryname</TD>";

        //���s�R�[�h��BR�^�O�ɒu��
        $tmpcomment = nl2br($tmpcomment);

        //�ȈՃ��[�h�ꍇ�A�\�����e���i�荞��
       if ($opt_ser_disp_lev == "simple" ) {
         $tmpcomment_full = $tmpcomment;
         $tmpcomment_array = str_split($tmpcomment,240);
         $tmpcomment = $tmpcomment_array[0];

         if ( isset($tmpcomment_array[1]) ) {
           $tmpcomment_full =  strip_tags($tmpcomment_full);
           $tmpcomment .= "�@<A href='infodisplay.php?infoid=$col[infoid]' title='$tmpcomment_full' target='_blank'>������ɏڍ�</A>";
         } 
       }

       $body .= "<TD width='670' align='left' valign='top' style='word-break:break-all'>$tmpcomment</TD>";
       if ( $tmpuriinfo != null ) {
         $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'><A href=urilink.php?infoid=$col[infoid] target='_blank' title=$tmpuriinfo>�N���b�N</A>";
       } else {
         $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>";
       }   
             
       if ( $searchtype == "cr" ) {
         $body .= "<br><br>" . $col['accdate'] . "<br>" . $col['acctime'] ; 
       }
                
       $body .= "</TD>";

       //�N���b�N�A�]���|�C���g�\���Z�N�V����(S)

       //�N���b�N��������ꍇ�͕\��
       if ( $col[clickcnt] != 0 ) {
         //�]���|�C���g������ꍇ�͕\��
         if ( $col[infovaluep] != 0 ) {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br><A href=urilink.php?infoid=$col[infoid] target='_blank'><img src='click16.png'>$col[clickcnt]</A><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'>$col[infovaluep]</A></TD>";
         }
         //�]���|�C���g���Ȃ��ꍇ
         else {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br><A href=urilink.php?infoid=$col[infoid] target='_blank'><img src='click16.png'>$col[clickcnt]</A><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'></A></TD>";
         }
       }
       //�N���b�N�����Ȃ��ꍇ
       else {
         //�]���|�C���g������ꍇ�͕\��
         if ( $col[infovaluep] != 0 ) {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'>$col[infovaluep]</A></TD>";
         }
         //�]���|�C���g���Ȃ��ꍇ
         else {
           $body .= "<TD width='78' align='left' valign='top' style='word-break:break-all'>$col[upddate]<br><br>";
           $body .= "<A href=infovalueadd.php?infoid=$col[infoid]&opt=\"$infovaluep_research_opt\" target='_blank'><img src='good16.png'></A></TD>";
         }
       }

       //�N���b�N�A�]���|�C���g�\���Z�N�V����(E)

       $body .= "<TD width='24' valign='top'>
                <INPUT id='button_sm' type='button' value='�ҏW' onclick='EditExec(\"$col[infoid]\");'>
                <INPUT id='button_sm' type='button' value='�폜' onclick='DeleteCheck(\"$col[infoid]\");'><br>
                <A href=infodisplay.php?infoid=$col[infoid] target='_blank'>�ڍ�</a>
                </TD></TR>";

      } //2017.9.9�ǋL �}�b�v���̃J�e�S�������ŉ����o�^����Ă��Ȃ��ꍇ�́A�\���ΏۊO�Ƃ����i�I�[�j

    } //while�̏I�[

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

    //���ʃZ�b�g��j�����܂�
    mysqli_free_result($rst);

    //�����L�[���[�h(�������ꍇ�j�̃��O�������݂�ǉ�
    $result = "�����q�b�g";
    $scene  = "������ʕ\��";
    if ( $log_write_flg == "on" ) {
      logw($userid,$dispname,$scene,$searchtype,$result,$tcnt,$keyword_org,$categid,$regdate,$comment);
    }
    //MySQL�Ƃ̐ڑ����������܂�
    $mysqli->close();

    //�y�[�W�̃i�r�Q�[�V������ǉ�(�㕔���ʁj
    $body .= "<p align='left'>";
    if ($page > 1) {
      $body .= $body_navi_1;
    }
    if ($totalpage > 1 and $page < $totalpage) {
      $body .= $body_navi_2;
    }
    $body .= "</p>";

  }
  $body .= "<p align='left'><a href='#'>���̃y�[�W�̐擪��</a></p></div>";

  return $body;
}


function per_keyword_disp_any($db_con_info,$host_url,$host_name,$userid,$dispopt,$rireki_cnt) {

  //�p�����[�^�I�v�V����
  $dispopt = "per_keyword"; //�g���Ă��Ȃ�

  $ROWSIZE = 10;

  //MySQL�ɐڑ����܂�
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

  //�L�[���[�h�\��
  $body .= "<TABLE id='keytable_" . $host_name . "' >";
  $colnum = 1;
  while($col = mysqli_fetch_array($rst)) {
    if ($colnum == 1) {
      $body .= "<TR>";
    }
    //�e���R�[�h���e��\������\��g�ݗ��Ă܂�
    $body .= "<TD align='left' valign='top' style='word-break:break-all'>
                <A href='search.php?dispclass=STA&keyword=$col[keyword]' target='_blank'>$col[keyword]</A>
                </TD>";
    if (++$colnum > $ROWSIZE) {
      //�P�s���\�������玟�̍s��
      $body .= "</TR>";
      $colnum = 1;
    }
  }
  if ($colnum != 1) {
    $body .= "</TR>";
  }
  $body .= "</TABLE>";

  //���ʃZ�b�g��j�����܂�
  mysqli_free_result($rst);

  //MySQL�Ƃ̐ڑ����������܂�
  $mysqli->close();

  //�y�[�W�{�����o�͂��܂�
  return $body;

 }


//�l���̏��敪�ŉ����\���ΏۂƂȂ��Ă��邩���ׁA����HTML�𐶐�
function body_search_class_any($host_url,$rireki_type,$perinfoclass,$searchclass) {

  $body = "<table><td  width='60'>";//�e�[�u����1�J�����ڂ̕��w��
  $body .= "<form name='kensaku' action='search.php' method='get'><A href='infoclass_disp_mnt.php' ><font color = 'black'>���敪</font></A></td>";

  $body .= "</td><td width='840'>";//�e�[�u����2�J�����ڂ̕��w��

  //�����N���X�̕\��BODY($search_class_chk�j�쐬
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
    //�����N���X�̑I��
    for ($i = 0; $i < sizeof($perinfoclass); $i++) {
      //�����敪�Ƀ`�F�b�N���ꂽ���̂̔���
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

  $body .= $search_class_chk;//�����N���X�̕\��BODY�t�^
  $body .= "<br><A href='searchmenu.php?rireki_type=all'><font color = 'black'>�S�`�F�b�N</A>�@�@<A href='searchmenu.php?rireki_type=clear'><font color = 'black'>�S�N���A</A>";

  $body .=  "�@�@�@<input id = 'button_sm' value='�ύX' type='submit'>";

  $body .= "</td></table>";

  return array($body,$search_class_chk);
}

//�������p��HTML�{��
function body_search_windows_any($db_con_info,$host_url,$host_name,$usersess,$dispopt,$opt_ser_disp_menu,$opt_ser_hist_num,$keyword_org) {
  if ( $opt_ser_disp_menu == 'details') {
    $body .=  "�@�@�\�����x���F<input type='radio' name='dispclass' value='STA' checked> �W��
                      <input type='radio' name='dispclass' value='ALL'>
                      <A title='�C���[�W�^�敪�^�o�^ID�\����\���������ꍇ'>�ڍ�</A><br>";
  }

  $body .= "<br>�@�@�@<input size='51' name='keyword' type='text' id='keyword' value=\"$keyword_org\">
            <input id = 'button_m' value='�@�@�����@�@' type='submit' class='withicon'>�@";

$body .= "<style type='text/css'>
input.withicon {
   background-image: url('search_16.png');
   background-repeat: no-repeat;
   background-position: 2px center;
   padding-left: 21px;
}
</style>";

  //�N���b�N����\��
  $body .= "<b>�N���b�N����&nbsp;</b><A href='search.php?click_rireki=cr'><font color = 'black'>�\��</A>";

  //�L�[���[�h����\��
  $body .="<b>�@�@��������</b>";
  $body_rireki .= per_keyword_disp_any($db_con_info,$host_url,$host_name,$usersess,$dispopt,$opt_ser_hist_num);

  $body .="
  <a href='#' id='link_view_" . $host_name . "' onClick='toggle_view_". $host_name . "() ;return false;' >�\��</a>
  <a href='#' id='link_hidden_" . $host_name . "' onClick='toggle_hidden_". $host_name . "();return false;' style='display:none'>��\��</a>";

/*
  $body .="
  <a href='#' id='link_view2' onClick='toggle_view2();return false;' >�\��</a>
  <a href='#' id='link_hidden2' onClick='toggle_hidden2();return false;' style='display:none'>��\��</a>";
*/

  $body .= "�@�@�@�@<a href='user_option.php' target='_blank'>�I�v�V�����ݒ�</A>"; 

  $body .= "</form>";

  $body .= "
          <SCRIPT language='JavaScript'>
             document.getElementById('keyword').focus();
          </SCRIPT>";

  $body .="<div id='rireki_" . $host_name . "' style = 'display:none'>�@$body_rireki</div>

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






//�l���Ƃ̌����敪�����擾���Z�b�g����(�V�j
function perinfoclass_set2($db_con_info,$userid) {

  //MySQL�ɐڑ����܂�
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
  $mysqli->set_charset("sjis"); // ���������h�~

  $sql = "SELECT * from tblaccess where ( userid = '$userid' and avaflg != 'delete' ) ORDER BY sort_num ";
  $rst = $mysqli->query($sql);

  $perinfoclass[0] = "�l";
  $perinfoclass[1] = "����";

  $i = 2 ;

  while($col = mysqli_fetch_array($rst)) {
    $perinfoclass[$i] = $col['class_name'];
    $i = $i + 1 ;
  }
   
  //���ʃZ�b�g��j�����܂�
  mysqli_free_result($rst);
  return $perinfoclass;
}


//DB�ڑ��֐��F�w��`�Amysql���^�C�v�iPHP���Â��ꍇ�Łj
function my_sql_connect_old($dbserver,$dbuser,$dbname,$dbpassword,$charset) {
  $link = mysql_connect($dbserver,$dbuser,$dbpassword);
  mysql_select_db($dbname,$link);
}

//DB�ڑ��֐��F�w��`�Amysqli���^�C�v
function my_sql_connect_any($db_con_info,$charset) {
  $mysqli = new mysqli($db_con_info[dbserver],$db_con_info[dbuser],$db_con_info[dbpassword],$db_con_info[dbname]);
  if ($mysqli->connect_errno){
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit;
  }
  $mysqli->set_charset($charset); // ���������h�~�isjis�p)
  return $mysqli;
}

//�Z�L�����e�B�΍� html���̃^�O������
function h($str)
{
  return htmlspecialchars($str, ENT_QUOTES, 'sjis');
}





function body_common_header() {
  //�g�b�v�y�[�W�̃w�b�_
  //�Z�b�V�������J�n���܂�
  session_start();

  $dev_host_mes = "";
  //���،n�̏ꍇ�̃��b�Z�[�W�쐬
  if( $_SERVER["HTTP_HOST"] == "10.90.233.52" ) {
    $dev_host_mes = "�@�@<font color='green'>���ؗp�T�C�g</font>";
  }
   
  //���O�\���p�^�[��
  $mysqli = my_sql_connect("sjis");
  $usersess = $_SESSION['USERSESS'];
  //�l�̖��O�̃Z�b�g  ���ׂ�������\��������̂ŏ������P�v
  $sql = "SELECT * FROM user where userid = '$usersess' ";
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  $namekanji = $col['namekanji'];//���O�̃Z�b�g
  $opt_disp_css = $col['opt_disp_css'];//CSS�̕\���^�C�v�Z�b�g
  $per_osss_name = substr($col['mail_pc_address'], 0, strcspn($col['mail_pc_address'],'@'));//���[���A�h���X�̖��O�����Z�b�g
  $per_osss_url = "file://storage03.psss.g01.fujitsu.local/" . $per_osss_name;

  
  //�w�b�_�����N���body�쐬
//  $ate_mes['0'] = "�g�D�ĕҎ葱���֘A";
  $ate_mes['0'] = "MIS2���Ɩ{���|�[�^��";
  $ate_mes['1'] = "�i���|�[�^��";
  $ate_mes['2'] = "SE�x���c�[��";
  $ate_mes['3'] = "�l�pOSSS";
  $ate_mes['4'] = "4�N����OSSS";
  $ate_mes['5'] = "2�V�X��OSSS";

//  $ate_url['0'] = "search.php?searchclass[]=����&keyword=$ate_mes[0]' target='_blank'";
  $ate_url['0'] = "urilink.php?infoid=1331";
  $ate_url['1'] = "urilink.php?infoid=1747";
  $ate_url['2'] = "urilink.php?infoid=1404";
  $ate_url['3'] =  $per_osss_url;
  $ate_url['4'] = "urilink.php?infoid=2043";
  $ate_url['5'] = "urilink.php?infoid=1300";
  
  
  if (isset($_SESSION['USERSESS'])) {
//            $body_userid = "<table style='margin-left : auto ; margin-right : 0 ; text-align : 0;'><td>���[�U�h�c</td><td> " .  $_SESSION['USERSESS']   . "</td></table>";

//  $body_userid = "<td>ID:</td><td> " .  $_SESSION['USERSESS']   . "����</td>"; //ID�\���p�^�[��
    $body_userid = "<td></td><td> " .  $namekanji  . "����</td>"; //���O�\���p�^�[��
  }

  $body = "
  <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
  <html lang='ja'>
  <head>
  <meta http-equiv='Content-Type' content='text/html; charset=Shift_JIS'>
  <meta http-equiv='Content-Style-Type' content='text/css'>
  <meta http-equiv='Content-Script-Type' content='text/javascript'>
  <meta name='keywords' content='���|�[�^��'>
  <meta name='description' content='�g�D���̌����|�[�^��'>
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
  <title>���|�[�^��</title>

  </head>
  <body>

  <div id='container'>

  <!-- �y�[�W�J�n -->
  <div id='page'>

  <!-- �w�b�_�J�n -->
  <div id='header'>

  <h1 class='siteTitle'><p>�@���|�[�^��". $dev_host_mes . "</P></h1>

  <ul class='guide'>
  <li class='first'><a href='site_mokuteki.php' target='_blank' style='text-decoration:none;'>���T�C�g�ɂ���</a></li>
  <li><a href='info_faq.php' style='text-decoration:none;'>FAQ</a></li>
  <li><a href='infostamap.php' style='text-decoration:none;'>�T�C�g�}�b�v</a></li>
  <li><a href='logout.php' style='text-decoration:none;'>���O�A�E�g</a></li>
  <li>" . $body_userid . "</li>
  </ul>


  <ul class='nl clearFix'>
	<li class='active'><a href='login.php?id=1'>�g�b�v</a></li>
	<li><a href='login.php?id=2'>�}�b�v</a></li>
	<li><a href='login.php?id=3'>����</a></li>
	<li><a href='login.php?id=4'>�o�^</a></li>
	<li><a href='extlink.php'>�T�C�g</a></li>
	<li><a href='userupd.php'>�ݒ�</a></li>
  </ul>";

  //�w�b�_�����N���̑g�ݍ���
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
  <!-- �w�b�_�I�� -->
  ";
  return array($body,$per_osss_url);  
}

//htmlheader�œǂ�ł���p�ɍ쐬�i���X�g�Ŏ󂯂�Ȃ����߁j
function htmlheader($pagetitle) {
  list ($body_header,$per_osss_url) = body_common_header();
  return $body_header;
}

//�g�b�v�̖{�̕���
function body_top_main($per_osss_url) {

  $usersess = $_SESSION['USERSESS'];

  //DB�ɐڑ�
  $mysqli = my_sql_connect("sjis");

  $body = "

  <!-- �R���e���c�J�n -->
  <div id='content'>

  <!-- ���C���J�����J�n -->
  <div id='main'>
  
  <div class='section topics'>

  <ul class='tabs clearFix' id='tabs'>
  <li class='first' id='tab1'><a href='#' onclick='topics(1); return false;'>�����T�C�g</a></li>
  <li id='tab2'><a href='#' onclick='topics(2); return false;'>�V�[���ʌ���</a></li>
  <li id='tab3'><a href='#' onclick='topics(3); return false;'>�e��ʒm</a></li>
  <li id='tab4'><a href='#' onclick='topics(4); return false;'>�e���</a></li>
  <li class='last' id='tab5'><a href='#' onclick='topics(5); return false;'>�}�C�^�O</a></li>
  </ul>

  <div class='topicArea'>

  <!-- �{�b�N�X1�J�n -->
  <div class='topic' id='topic1'>

  <p>�y�e�팟���T�C�g�z</p>
  <!-- <p>�e�팟���T�C�g</p>  -->
  <ul>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/' target='_blank' style='text-decoration:none;'>IKB�g�b�v</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/itimap' target='_blank' style='text-decoration:none;'>IKB-ITIMAP</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/cloud.aspx' target='_blank' style='text-decoration:none;'>IKB-�N���E�h</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://ikb.jp.fujitsu.com/metaarc.aspx' target='_blank' style='text-decoration:none;'>IKB-�O�����h�f�U�C��</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://dds-pre.b.css.fujitsu.com/sisearch/' target='_blank' style='text-decoration:none;'>SE���ރT�[�`</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://s-navi.solnet.ssg.fujitsu.com/' target='_blank' style='text-decoration:none;'>SE���|�[�^��</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://mysite.gcs.g01.fujitsu.local/personal/search/Pages/advanced.aspx?k=ALL%28%E4%B8%AD%E5%8E%9F%E3%83%93%E3%83%AB%29%20%28Path%3A%22ikb%2Esolnet%2Essg%2Efujitsu%2Ecom%2F%22%20OR%20Path%3A%22s%2Dnavi%2Esolnet%2Essg%2Efujitsu%2Ecom%2F%22%29' target='_blank' style='text-decoration:none;'>�x�m�ʑS�Ќ���</a>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='https://www.google.co.jp/' target='_blank' style='text-decoration:none;'>google</a><br>
  </ul>


  </div>
  <!-- �{�b�N�X1�I�� -->

  <!-- �{�b�N�X2�J�n -->
  <div class='topic' id='topic2'>
  <p>�y�V�[���ʊe�팟���z</p>
  <!--
  <p>�{�b�N�X�i�{���̈�j�̍�����11em�ɐݒ肳��Ă��܂��B��ԍ����̂���{�b�N�X�ɍ��킹�Ē������Ă��������B</p>
  <p>���e���{�b�N�X�̍�������͂ݏo���ꍇ�́A�X�N���[���ŕ\������邱�ƂɂȂ�܂��B�i�u�^�u����3�v���Q�Ɓj</p>
  -->
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;����񋟗\��<br>
  
  </div>
  <!-- �{�b�N�X2�I�� -->
  
  <!-- �{�b�N�X3�J�n -->
  <div class='topic' id='topic3'>

  <!--
  <p>���̃T���v���ł́A�^�u�G���A�̕������ς��Ƀ^�u����ׂĂ��܂��B�^�u�G���A�̕��i468px�j��5�Ŋ���؂�Ȃ��̂ŁA�ŏ��ƍŌ�̍��ڂɃN���X���ifirst�Alast�j��ݒ肵�č��ڕ��𒲐����Ă��܂��B�i����؂��ꍇ�͂��̃N���X���̐ݒ�͕K�v����܂���j</p>
  <p>�������ς��ɕ��ׂȂ��i�E���ɗ]�����c��j�ꍇ�́ACSS�R�[�h���́u�Ō�̃����N�G���A�v�̐ݒ���폜���Ă��������B�i������폜���Ȃ��ƁA�Ō�̃^�u�̉E���������\������܂���j</p>
  <p>�܂��A�^�u���̃e�L�X�g�̍��E�ɂ͗]�T���������Ă��������B�M���M���ɐݒ肷��ƁA�u���E�U�̕����T�C�Y��傫�������ۂɐ܂�Ԃ��������Ă��܂��܂��i�\��������Ă��܂��܂��j�B</p>
  <p>�u���E�U�̕����T�C�Y��傫�����ĕ\���m�F���s���Ă��������B</p>
  -->

  <a>�y�g�D���ł̒ʒm���y�у����N����f�ځz</a>
  <ul>
<!--  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='infodisplay.php?infoid=531' target='_blank'>�y�v���Z�X���P�z���k������/PA��v���Z�X������</a><br> -->
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/itkibanbg/Lists/BG' target='_blank'>����BG)IT�{���|�[�^���ʒm����</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/fwest/portal/Lists/fwBBS' target='_blank'>����BG)�|�[�^���ʒm����</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/jst/recognization_gyomu/Lists/List/AllItems.aspx' target='_blank'>10��1���t�g�D�ĕ҃|�[�^�� ���m�点�i�f���j</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/itsolution/ITSBUPortal/Lists/News/view2.aspx' target='_blank'>IT�V�X�e�����Ɩ{�� : ���Ɩ{�����m�点 </a><br>
  </ul>
  </div>
  <!-- �{�b�N�X3�I�� -->
  
  <!-- �{�b�N�X4�J�n -->
  <div class='topic' id='topic4'>
  <a>�y�g�D�y�ьl�̊e��󋵋y�у����N����f�ځz</a>
  <ul>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='portforio_map_main.php' target='_blank'>2017�N�x ���V�X���@����LCM(�ŐV��)</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki_tougetu.php' target='_blank'>2017�N�x SI���ƕ��@������v����(�ŐV��)</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki.php' target='_blank'>2013-2016�N�x�@SI���ƕ��@���v����</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki_cs_ruikei_ranking.php?sorttype=ruikei_uriage' target='_blank'>2014-2017�N�x�@SI���ƕ��@�ڋq�ʎ��у����L���O</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='portforio_dash0.php' target='_blank'>���ƕ��@�|�[�g�t�H���I</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='portforio_dash0_gyousyu1.php' target='_blank'>���ƕ��@�Ǝ�ʃr�W�l�X��</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='soneki_dash_disp_chart.php' target='_blank'>SI���ƕ��@2016�N�x���сi�O���t����j</a><br>
  
  </ul>

  </div>
  <!-- �{�b�N�X4�I�� -->


  <!-- �{�b�N�X5�J�n -->
  <div class='topic clearFix' id='topic5'>

  <!--
  <div class='catch'>
  <p><a href='#'><img src='noimage.jpg' alt='�T���v��' width='100' height='70'></a></p>
  <p><a href='#'>�L���v�V����</a></p>
  </div>
  --> 

  <div class='text'>
  <p>�y�l�̃����N���z</p>
  <!-- <p>�{�b�N�X���̉E���Ɏʐ^��z�u���邱�Ƃ��ł��܂��B�i�ʐ^�̕���128px�܂Łj</p>  -->

  <ul>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='mylink_display.php' target='_blank'>�}�C���C�ɓ���</a><br>
  <img src='image/menu_triangle6.gif'>&nbsp;&nbsp;<a href='" . $per_osss_url . "' target='_blank'>�}�C�t�H���_�i�losss�t�H���_�j</a><br>
 
  </ul>
  </div>

  </div>
  <!-- �{�b�N�X5�I�� -->

  </div>

  <!-- ���^�u�{�b�N�X���g�p����ꍇ�͍폜���Ȃ��ł������� -->
  <script type='text/javascript'>
  topics(1); //�^�u�{�b�N�X�p�̊֐��̌Ăяo��
  </script>

  </div>";


	$body .="

	<div class='section normal'>";

  //������
  $body .= index_search_windows($rireki_type,$perinfoclass,$searchclass,$usersess,$dispopt,$opt_ser_disp_lev,$opt_ser_hist_num,$keyword_org);

  //�����̓��t���Z�b�g
  $today = date('Y/m/d');

//  $where_newinfo = body_newinfo_where();
  list($tcnt1,$tcnt2) = newinfo_check();

/*
  $sql = "select Count(*) AS cnt from tblinfo where $where_newinfo ";
  dp(sql,$sql);
  //SQL���𔭍s���܂�
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
  <h2>�V�����</h2>
  </div>";
  
  $body .="<dl class='clearFix'>";

  if ( $tcnt1 > 0 or $tcnt2 > 0) {
    $body .="<dt>$today</dt>";
  }
  if ( $tcnt1 > 0 ) { 
    $body .="<dd>�������̍X�V���<b><a href='search.php' target='_blank'>" . $tcnt1 . "��</a></b>����܂�</dd>";
  }
  if ( $tcnt2 > 0 ) {
    $body .="<dd>�ߋ�1�T�Ԃł̍X�V���<b><a href='search.php' target='_blank'>" . $tcnt2 . "��</a></b>����܂�</dd>";
  }

  $body .="<dt>2018/04/18</dt><dd><b><font color='navy'><a href='urilink.php?infoid=1961' target='_blank' style='text-decoration:;'>�헪��c���\����(4/12)</a></font></b>���o�^����܂����B</dd>";
  $body .="<dt>2018/03/30</dt><dd><b><font color='navy'><a href='urilink.php?infoid=1914' target='_blank' style='text-decoration:;'>MIS2���Ɩ{�� 2018�N�x���ƕ��j</a></font></b>��o�^���܂����B</dd>";
  $body .="<dt>2017/12/04</dt><dd><b><font color='navy'>�e������񋤗L�̂��߂̏��敪��ǉ�</font></b>���܂����B</dd>";
  $body .="<dd>�����ł̏�񋤗L�Ɋ��p���Ă��������B</dd>";
  $body .="<dt>2017/11/20</dt><dd>�ŐV�̏��́A<b><font color='navy'>�����{�^���N���b�N</font></b>�ŕ\������܂��B</dd>";
  $body .="<dd>���y�[�W����<b><font color='navy'>�E�H�b�`���[�h�o�^�A����</font></b>�\�ł��B</dd>";

/*
  $body .="<dt>2017/10/23</dt><dd>���������j��o�^���܂����B<a href='urilink.php?infoid=1403' target='_blank' style='text-decoration:;'>�����e���������j</a></dd>";
  $body .="<dt>2017/10/13</dt><dd>�������ƕ��j��o�^���܂����B<a href='urilink.php?infoid=1358' target='_blank' style='text-decoration:;'>���ƕ��j</a></dd>";
*/
  $body .="<dt>2017/10/02</dt><dd>���ƕ��̏��|�[�^�������p�J�n���܂����B<a href='site_mokuteki.php' target='_blank' style='text-decoration:;'>�T�C�g�T�v����</a></dd>
  </dl>
  </div>";

  //$body .="<dt>2017/10/16</dt><dd>�����{�a�f 2017�N������Z�󋵂�o�^���܂����B<a href='urilink.php?infoid=1375' target='_blank' style='text-decoration:;'>������Z��</a></dd>
 
	
	$body .="


	<div class='section normal'>

	<div class='heading'>
	<h2>�Z�p���k</h2>
	</div>
	<p>IT�{���Z�p���[�����O���X�g(ITtech)�ɑ��k���Ă��������B<p><a href='mailto:ittech-infra@ml.css.fujitsu.com'>ittech-infra@ml.css.fujitsu.com</a></p>
	</div>

	<div class='section normal'>

	<div class='heading'>

	<h2>���|�[�^���ɂ���</h2>

	</div>

	  <p>���|�[�^���͑g�D�̏�񋤗L�������ƌl�̏�񌟍���������i�߂�T�C�g�ł��B</P>
	 
	  <p><a href='file://v130.osss.g01.fujitsu.local/f02489/99_����Ճ|�[�^��/03_���SI���ƕ�/10_���ƕ�����/12_�d�_�{�􊈓�/���[�N�X�^�C���ϊv���i/��񋤗L���/���ƕ���񋤗L��Ր���.ppt'>�ڍא����͂�����</a></p>

	</div>

	</div>
	<!-- ���C���J�����I�� -->";

  //�������j���[�\���p�̃`�F�b�N
  $sql = "SELECT member_class FROM user where userid = \"$usersess\" ";
  $rst = $mysqli->query($sql);
  $col = mysqli_fetch_array($rst);
  if ( $col['member_class'] == "2itssys-kanbu" ) {
    $kanbu_flg = "yes";
  }    
  mysqli_free_result($rst);

  //���ʂ̃J�e�S������ǂݍ���
  $sql = "SELECT * FROM tblcategory order by categoryid ";
  $rst = $mysqli->query($sql);

  $cnt = 1 ;
  while ($col = mysqli_fetch_array($rst)) { 
    $topmenu[$cnt] = $col['categoryname'];
    $cnt++;
  }
	$body .="

	<!-- �T�C�h�o�[(A)�J�n -->
	<div id='nav'>

	<div class='section subMenu'>

	<h2>���J�e�S��</h2>

	<ul class='nl'>";
	
	for ($i = 1; $i < $cnt; $i++) {
	  if ( $topmenu[$i] == "�����Ǘ�" ) {
	    if ( $kanbu_flg == "yes" ) {
	      $body .="<li align='left'><a href='search.php?searchclass[]=����&keyword=$topmenu[$i]' target='_blank'>$topmenu[$i]</a></li>";
        }
      } else {
        $body .="<li align='left'><a href='search.php?searchclass[]=����&keyword=$topmenu[$i]' target='_blank'>$topmenu[$i]</a></li>";
      }   
	}  


	$body .="
	</ul>

	</div>

	</div>
	<!-- �T�C�h�o�[(A)�I�� -->


	<hr class='clear'>

	</div>
	<!-- �R���e���c�I�� -->


	<!-- �T�C�h�o�[(B)�J�n -->
	<div id='aside'>

	<div class='section strong'>

	<h2>�d�v�L��</h2>

	<dl class='clearFix'>
    <!-- �C���[�W���������ꍇ�͈ȉ� 
	<dt><a href='#'><img src='noimage.jpg' alt='�T���v��' width='90' height='60'></a></dt>
	<dd><a href='#'>�d�v�ō��m���������e�ɑ΂��Ă������烊���N�B</a></dd>
	-->
<!--
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='http://portalsite.gcs.g01.fujitsu.local/sites/itkibanbg/_layouts/listform.aspx?PageType=4&ListId={2CB96FAB-7EAD-4A95-9E8D-615D6E83B208}&ID=241&ContentTypeID=0x010400C6E432C75C1007479CB9F577EA8EBF31' target='_blank'>�y���ӊ��N�zIPCOM�d�v��Q</a><br>
-->
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='search.php?searchclass[]=����&keyword=�g�D�ĕҎ葱���֘A' target='_blank' style='text-decoration:none;'>�g�D�ĕҎ����葱���ɂ���</a><br>
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='infodisplay.php?infoid=1242' target='_blank' style='text-decoration:none;'>�Óc�햱����̃��b�Z�[�W</a><br>
<!--
    <img src='image/menu_triangle2.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1917' target='_blank' style='text-decoration:none;'>�R���{�����A�����{��������̃��b�Z�[�W<br>�@(2���S�̒���)</a><br>
-->


	</dl>

	</div>

	<div class='section pickup'>

	<h2>�����N</h2>
	<ul>

	<li><dl class='clearFix'>
	<!--
	<dt><a href='#'><img src='noimage.jpg' alt='�T���v��' width='90' height='60'></a></dt>
	<dd><a href='#'>�s�b�N�A�b�v�R���e���c1</a><br>�s�b�N�A�b�v�R���e���c�Ɋւ���ȒP�Ȑ����B</dd>
	-->
	<!-- <a>�y�ǂ�����T�C�g�z</a><br> -->
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='cal0_2018.php' target='_blank' style='text-decoration:none;'>�^�X�N�_�b�V���{�[�h</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='cal0_1IT.php' target='_blank' style='text-decoration:none;'>�^�X�N�_�b�V���{�[�h(3�N��1�V�X)</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='cal0_road.php' target='_blank' style='text-decoration:none;'>��񋤗L���P�{��ꗗ</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1453' target='_blank' style='text-decoration:none;'>��SI���ƕ� OSSS�t�H���_</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1915' target='_blank' style='text-decoration:none;'>�}���C�g10F �{����c���\��</a><br>";


	if ( $kanbu_flg == "yes" ) {
  	$body .="<img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1321' target='_blank' style='text-decoration:none;'>MIS2����OSSS�t�H���_</a><br>";
  }
	$body .="
    <!-- 2017.11.3 �R�����g�A�E�g�@���܂�g���Ă��Ȃ��Ƒz��
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='link_itp.php' target='_blank' style='text-decoration:none;'>IT�|�[�^�������N�W</a><br>
    -->
	</dl></li>


	<li><dl class='clearFix'>
	<!--
	<dt><a href='#'><img src='noimage.jpg' alt='�T���v��' width='90' height='60'></a></dt>
	<dd><a href='#'>�s�b�N�A�b�v�R���e���c2</a><br>�s�b�N�A�b�v�R���e���c�Ɋւ���ȒP�Ȑ����B</dd>
	-->
    <a>�y�e��K��E����z</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1977' target='_blank' style='text-decoration:none;'>MIS2�v���Z�X�^�p�����(���ي)</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=2052' target='_blank' style='text-decoration:none;'>4�N�����^�p�v���Z�X�K��</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=1488' target='_blank' style='text-decoration:none;'>�G�X�J���[�V�����</a><br>



	</dl></li>
	<li class='last'><dl class='clearFix'>
	<!-- ���Ō�̍��ځili�v�f�j�ɃN���X���ilast�j��ݒ肵�Ă������� -->
	<!--
	<dt><a href='#'><img src='noimage.jpg' alt='�T���v��' width='90' height='60'></a></dt>
	<dd><a href='#'>�s�b�N�A�b�v�R���e���c3</a><br>�s�b�N�A�b�v�R���e���c�Ɋւ���ȒP�Ȑ����B</dd>
	-->
    <a>�y�������߃����N�z</a><br> 
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='http://seshien.fwest.css.fujitsu.com/knowledge/open.knowledge/list' target='_blank' style='text-decoration:none;'>Knowledge�iSE��ƌ������j<br>�@(��SE�x���c�[�����O�I���v)</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='urilink.php?infoid=384' target='_blank' style='text-decoration:none;'>��SE�x���c�[��</a><br>
<!--    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='info_ranking.php' target='_blank' style='text-decoration:none;'>���|�[�^�����A�N�Z�X�����L���O</a><br> -->
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='search.php?dispclass=&keyword=&orderby_opt=click' target='_blank' style='text-decoration:none;'>�T�C�g���{���������L���O</a><br>
    <img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='search.php?dispclass=&keyword=&orderby_opt=valuep' target='_blank' style='text-decoration:none;'>�T�C�g���]���������L���O</a><br>




	</dl></li>

	</ul>

	</div>

	<div class='section emphasis'>

	<h2>���b�Z�[�W</h2>
	<ul>
<!--
	<li ><a href='urilink.php?infoid=1455' target='_blank' style='text-decoration:none;'>�ēc�{�����㗝���b�Z�[�W</a></li>
-->
	<li ><a href='urilink.php?infoid=2055' target='_blank' style='text-decoration:none;'>2018�N�x���ƕ����j</a></li>
<!--
	<li ><a href='urilink.php?infoid=1916' target='_blank' style='text-decoration:none;'>���ƕ������b�Z�[�W</a></li>
-->
	<li ><a href='# 'target='_blank' style='text-decoration:none;'>�������b�Z�[�W</a></li>
	</ul>

	<div class='section normal'>

	<h2>�G�X�J���[�V����</h2>

	<p><a href='urilink.php?infoid=1488' target='_blank' style='text-decoration:none;'>��肪�N��������A���킸��i�ɃG�X�J���[�V�������Ă��������B</a></p>

	</div>


	<div class='section normal'>

	<h2>SE���[�N�X�^�C���ϊv</h2>

	<p><img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='#'  align='left' style='text-decoration:none;'>�d���̐i�ߕ�</a></p>
	<p><img src='image/menu_triangle6_box.gif'>&nbsp;&nbsp;<a href='#'  align='left' style='text-decoration:none;'>�d�����P���</a></p>

	</div>

<!--
	<div class='section normal contact'>
	<h2>�Z�p���k</h2>
	<p>�C�y�Ƀ��[��</p>
	<p class='tel'>ittech-infra@ml.css.fujitsu.com</p>
	<p class='form'><a href='mailto:ittech-infra@ml.css.fujitsu.com'>�C�y�ɑ��k</a></p>
	</div>
-->

	<div class='section normal'>

	<h2>���P�v�]</h2>

	<p><a href='infonewyobo.php'>�|�[�^���Ɋւ��Ẳ��P�A�V�K�v�]�ɑ΂��Ă͂������烊���N�B</a></p>
<!--	<p><a href='#' style='text-decoration:none;'>�|�[�^���Ɋւ��Ẳ��P�A�V�K�v�]�ɑ΂��Ă͂������烊���N�B</a></p>-->

	</div>


	<hr class='none'>

	</div>

	<!-- �T�C�h�o�[(B)�I�� -->

	</div>
";

	return $body;

	}

function body_top_footer() {

$body .="	<!-- �t�b�^�J�n -->
	<div id='footer'>

	<ul class='nl'>
	<li class='first'><a href='index.php'>�z�[��</a></li>
	<li><a href='site_mokuteki.php' target='_blank'>�T�C�g�ړI</a></li>
<!--	<li><a href='index.php'>�^�c�X�^�b�t</a></li>  -->
	<li><a href='infonewyobo.php'>���P�^�v�]�^����</a></li>
<!--	<li><a href='index.php'>�ē�</a></li>  -->
	</ul>

	<ul class='nl guide'>
	<li class='first'><a href='info_faq.php'>FAQ</a></li>
<!--	<li><a href='index.php'>�v���C�o�V�[�|���V�[</a></li> -->
	<li><a href='infostamap.php'>�T�C�g�}�b�v</a></li>
<!--	<li><a href='index.php'>���ӌ�</a></li> -->
	</ul>

	<address>
	GSI���� �O���[�o���f���o���[G  ��j�l�h�r���Ɩ{��  ��l�N���E�h�C���e�O���[�V�������ƕ�
	<br>
	</address>


	</div>

	</div>

	";

	return $body;
	//return array($imagefile,$errmsg);  

	}
//�g�b�v�̌������p
function index_search_windows($rireki_type,$perinfoclass,$searchclass,$usersess,$dispopt,$opt_ser_disp_lev,$opt_ser_hist_num,$keyword_org) {

  $body .= "<table><form name='kensaku' action='search.php' method='get'>";

  //�������p��HTML�{��
  $body .=  "�|�[�^���������@<input size='48' name='keyword' type='text' id='keyword' value=\"$keyword_org\">&nbsp;<input id = 'button_m' value='�@�����@' type='submit' ><A title='and����:�X�y�[�X&#13;&#10;or�����F�u�A�v�ŉ\�ł��B'><img src='question16.png'></a>";
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
	//�e�y�[�W�̃t�b�^����HTML��g�ݗ��Ă�

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