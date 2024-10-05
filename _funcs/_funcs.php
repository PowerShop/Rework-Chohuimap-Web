<?php

function rdr($url, $time)
{
    echo "<script type='text/javascript'>
		setTimeout(function(){
				location.href = '$url';
				}, $time);
</script>";
}

function encode($password)
{
    $en = sha1($password);

    return $en;
}

function query($sql, $array = array())
{
    global $api;
    $q = $api->sql->prepare($sql);
    $q->execute($array);

    return $q;
}

function DateThai()
{
    $strDate = date("F j, Y, g:i a");
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonth = date("n", strtotime($strDate));
    $strDay = date("j", strtotime($strDate));
    $strHour = date("H", strtotime($strDate));
    $strMinute = date("i", strtotime($strDate));
    $strSeconds = date("s", strtotime($strDate));
    $strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
    $strMonthThai = $strMonthCut[$strMonth];
    return "$strDay $strMonthThai $strYear, $strHour:$strMinute";
}
