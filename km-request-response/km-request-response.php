<?php
/**
 * Plugin Name: Kushmapper Request Form Response
 * Description: 
 * Author: SlyFox Request From Response
 * Version: 0.1.1
 */

add_shortcode('request-response', 'requestResponse');

function requestResponse()
{

    /* Check if the form has been submitted */
    if(array_key_exists('submit',$_POST))
    {
        // echo "<pre>"; print_r($_POST); echo "</pre>";
        $name = $_POST['author'];
        // echo $name;
    }

    ob_start(); 

    ?>
    <script>           
        function kmData() {
            return {
                name: '<?php echo $name; ?>',
            };
        }
    </script>

    <div class="columns" x-data="kmData()">
        <div class="km-logo column is-full">     
            <template x-if = "name">
                <h1 x-text="'Hi, ' + name"> </h1>
                <h3> Your request is received !!</h3>
            </template>
        </div>
    </div>

    <?php

    $html = ob_get_clean();
    return $html;
}
