
<?php
include('functions.php');
$dblink = db_connect('document_management_system');
$sql = "Select * from `downloaded_files` where `date_downloaded` < '2022-12-01 0:00:00'";
$result=$dblink->query($sql) or
    die();
$loanArray=array();
while($data=$result->fetch_array(MYSQLI_ASSOC))
{
    $loanArray[] = $data['client_id'];
}
$loanUnique=array_unique($loanArray);
echo '<h3>Stran Dutton - @DGA804 - Data Reporting</h3>';
echo '<h1>All Loan Numbers:</h1>';
echo '<ul>';
foreach($loanUnique as $key=>$value)
{
    $sql = "Select count(`client_id`) from `downloaded_files` where `client_id` like '%$value' and `date_downloaded` < '2022-11-30 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    $loanCount++;
    //echo'<div>Loan Number '.$value.' has '.$tmp[0].' documents.</div>';
    echo'<li>'.$value.'</li>';
    $num_docs_per_loan += $tmp[0];
}
echo '</ul>';
echo'<p style="margin-left: 30px"><strong>Total number of loan accounts: </strong>'.$loanCount.'</p>';
$avg_num_docs_per_loan=number_format($num_docs_per_loan/$loanCount);
echo'<p style="margin-left: 30px"><strong>Average number of documents per loan account: </strong>'.$avg_num_docs_per_loan.'</p>';

$sql = "Select * from `downloaded_files` where `date_downloaded` < '2022-11-30 0:00:00'";
$result=$dblink->query($sql) or
    die();
$documentSizes = array();
while($data=$result->fetch_array(MYSQLI_ASSOC))
{
    $documentSizes[] = $data['file_size'];
}
foreach($documentSizes as $key=>$size)
{
    $sizeSum+=$size;
    $filecount++;
}
$total_megabytes = $sizeSum/1048576;
$cleaned_total_megabytes = number_format($total_megabytes,2);
$avg_size_across_all_docs = number_format(($sizeSum/$filecount));
$avg_size_across_all_docs_MB = number_format((($sizeSum/$filecount)/1048576),4);
echo'<h2>Document Sizes</h2>';
echo'<p style="margin-left: 30px"><strong>Total File Storage:</strong> '.$sizeSum.' B or '.$cleaned_total_megabytes.' MB</p>';
echo'<p style="margin-left: 30px"><strong>Average File Size:</strong> '.$avg_size_across_all_docs.' B or '.$avg_size_across_all_docs_MB.' MB  across '.$filecount.' documents.</p>';
echo'<p style="margin-left: 30px"><strong>Total Number of Documents:</strong> '.$filecount.'</p>';
echo '<p>***Lists of complete / incomplete loans are at the very bottom of pdf!***</p>';

$incomplete_loans = array();
$complete_loans = array();

$total_credit_documents = 0;
$total_closing_documents = 0;
$total_title_documents = 0;
$total_financial_documents = 0;
$total_personal_documents = 0;
$total_internal_documents = 0;
$total_legal_documents = 0;
$total_other_documents = 0;

foreach($loanUnique as $key=>$value)
{
    echo '<hr>';
    echo '<h2>Loan # '.$value.'</h2>';
    
    $has_credit = false;
    $has_closing = false;
    $has_title = false;
    $has_financial = false;
    $has_personal = false;
    $has_internal = false;
    $has_legal = false;
    $has_other = false;
    
    
    $sql = "Select count(`client_id`) from `downloaded_files` where `client_id` like '%$value' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    //echo'<h4>TOTAL Documents: '.$tmp[0].'</h1>';
    $total_docs_for_this_loan = $tmp[0];
    if($total_docs_for_this_loan < $avg_num_docs_per_loan)
    {
        echo '<p style="margin-left: 30px">Total Documents: '.$total_docs_for_this_loan.' <strong>(BELOW AVERAGE)</strong><br></p>';
    }
    elseif($total_docs_for_this_loan == $avg_num_docs_per_loan)
    {
        echo '<p style="margin-left: 30px">Total Documents: '.$total_docs_for_this_loan.' <strong>(SAME AS AVERAGE)</strong><br></p>';
    }
    else
    {
        echo '<p style="margin-left: 30px">Total Documents: '.$total_docs_for_this_loan.' <strong>(ABOVE AVERAGE)</strong><br></p>';
    }
    
    echo '      </div>';  //closing card title
    echo '<ul>';

    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Title' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Title Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_title = true;
        $total_title_documents+=$tmp[0];
    }
    
    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Credit' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Credit Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_credit = true;
        $total_credit_documents+=$tmp[0];
    }
    
    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Closing' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Closing Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_closing = true;
        $total_closing_documents+=$tmp[0];
    }
    
    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Financial' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Financial Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_financial = true;
        $total_financial_documents+=$tmp[0];
    }
    
    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Personal' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Personal Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_personal = true;
        $total_personal_documents+=$tmp[0];
    }
    
    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Internal' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Internal Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_internal = true;
        $total_internal_documents+=$tmp[0];
    }

    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Legal' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Legal Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_legal = true;
        $total_legal_documents+=$tmp[0];
    }


    $sql = "Select count(`file_category`) from `downloaded_files` where `client_id` like '%$value' and `file_category` like '%Other' and `date_downloaded` < '2022-12-01 0:00:00'";
    $rst=$dblink->query($sql) or
        die();
    $tmp=$rst->fetch_array(MYSQLI_NUM);
    echo'<li>Other Documents: '.$tmp[0].'</li>';
    if($tmp[0]>0)
    {
        $has_other = true;
        $total_other_documents+=$tmp[0];
    }
    echo '</ul>';
    
    if($has_other && $has_legal && $has_internal && $has_personal && $has_financial && $has_closing && $has_credit && $has_title)
    {
        $complete_loans[] = $value;
        echo '<p style="margin-left: 30px">No missing documents.</p>';
    }
    else
    {
        $missing = '';
        $incomplete_loans[] = $value;
        
        if(!$has_other)
        {
            $missing .= 'Other ';
        }
        if(!$has_legal)
        {
            $missing .= 'Legal ';
        }
        if(!$has_internal)
        {
            $missing .= 'Internal ';
        } 
        if(!$has_personal)
        {
            $missing .= 'Personal ';
        }
        if(!$has_financial)
        {
            $missing .= 'Financial ';
        }
        if(!$has_closing)
        {
            $missing .= 'Closing ';
        }
        if(!$has_credit)
        {
            $missing .= 'Credit ';
        }
        if(!$has_title)
        {
            $missing .= 'Title ';
        }
        $missing=str_replace(" ",", ",$missing);
        $missing=substr($missing, 0, -2);
        $missing .= '.';
        
        echo '<p style="margin-left: 30px"><strong>Missing Documents</strong>:  '.$missing.'</p>';

    }
}
echo '<hr>';
echo '<h2>Totals of each type of document across all loans:</h2>';
echo '<ul>';
echo "<li>Credit: $total_credit_documents</li>";
echo "<li>Closing: $total_closing_documents</li>";
echo "<li>Title: $total_title_documents</li>";
echo "<li>Financial: $total_financial_documents</li>";
echo "<li>Personal: $total_personal_documents</li>";
echo "<li>Internal: $total_internal_documents</li>";
echo "<li>Legal: $total_legal_documents</li>";
echo "<li>Other: $total_other_documents</li>";
echo '</ul>';
echo '<h2 id="completeLoans"> Complete Loans:</h2>';
echo '<ul>';
foreach($complete_loans as $key=>$value)
{
    echo '<li>'.$value.'</li>';
}
echo '</ul>';
echo '<h2> Incomplete Loans:</h2>';
echo'<ul>';
foreach($incomplete_loans as $key=>$value)
{
    echo '<li>'.$value.'</li>';
}
echo '</ul>';

?>