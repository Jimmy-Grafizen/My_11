<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('DiscountedEntryFees')){
 function DiscountedEntryFees($entry_fees, $more_entry_fees){
 	if( $entry_fees>0 ){
 		return number_format( ( ( $entry_fees*100 )/( 100-$more_entry_fees ) ) ,2);
 	}
 	return 0;
 }
}

if (!function_exists('TotalAmountEntryFeeNoOfSpots')){
 function TotalAmountEntryFeeNoOfSpots($entry_fees, $NoOfSpots){
    if( $entry_fees>0 ){
        return number_format( ( $entry_fees * $NoOfSpots ) ,2);
    }
    return 0;
 }
}



if ( ! function_exists('dd'))
{
    function dd($var = '',$die=false)
    {
    	echo "<pre>";
       	 print_r($var);
        echo "</pre>";
        if($die)
        	die();
    }   
}
/*KK
* Get row from table where id is id of array
*/
if (!function_exists('getRecordOnId'))
{
    function getRecordOnId($table, $where, $select='*'){
        $CI =& get_instance();
        $CI->db->from($table);
        $CI->db->select($select);
        $CI->db->where($where);
        $query = $CI->db->get();
        return $query->row();
    }
}

if (!function_exists('array_column_counts'))
{
    function array_column_counts($array) {

        $number_of_true = 0;
         foreach ($array as $val) {
            if($val->level and $val->menu){
                $number_of_true++;
            }
        }
        return $number_of_true;
    }
}