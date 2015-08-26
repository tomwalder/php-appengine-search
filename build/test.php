<?php
/**
 * Trivial search page
 */
require_once('../vendor/autoload.php');

$str_query = isset($_GET['q']) ? $_GET['q'] : '';

try {
    $obj_index = new \Search\Index('books1');
    $obj_response = $obj_index->search($str_query);

    $arr_debug = $obj_index->debug();

} catch (\Exception $obj_ex) {
    syslog(LOG_CRIT, $obj_ex->getMessage());
    $obj_response = $obj_ex;
}

?><form method="GET" action="/"><input type="text" name="q" id="q" placeholder="Query" value="<?php echo htmlspecialchars($str_query); ?>" />
<input type="button" onclick="javascript:run();" value="Go" >
</form>
<script>function run() { top.location.href = "/?q=" + document.getElementById('q').value; } </script>
<hr/>
<table border="1">
    <tr>
        <td valign="top">RESULTS<br/><pre><?php print_r($obj_response); ?></pre></td>
        <td valign="top">REQUEST<br/><pre><?php print_r($arr_debug[0]); ?></pre></td>
        <td valign="top">RESPONSE<br/><pre><?php print_r($arr_debug[1]); ?></pre></td>
    </tr>
</table>
<pre><?php


