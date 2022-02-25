<?php 
    ini_set('max_execution_time', 1500000);
    ini_set('memory_limit', '1024M');
    ini_set('mysql.connect_timeout', 1500000);
    ini_set('default_socket_timeout', 1500000);
    ini_set('mysql.reconnect', 1);
    ini_set('mysql.wait_timeout', 1500000);
    ini_set('wait_timeout', 1500000);
    ini_set('mysql.max_allowed_packet', '2024MB'); 
    ini_set('innodb_lock_wait_timeout', 1500000); 
    ini_set('mysql.innodb_lock_wait_timeout', 1500000); 
    ini_set('display_errors', 1);
    
    error_reporting(E_ALL);
    require(__DIR__ .'/config.php');
    require(__DIR__ .'/cdiscount/vendor/autoload.php');
    require(__DIR__ .'/cdiscount/sdk/autoload.php');

    $client = new \Sdk\ApiClient\CDSApiClient();
    $token = $client->init();

    
    if ($token == null || !$client->isTokenValid()) {
        echo "Oups, souci lors de la génération du token";
        die;
    }

    $productPoint = $client->getProductPoint();

    /******* GET ALLOWED CATEGORY TREE *******/

    $getAllowedCategoryTreeResponse = $productPoint->getAllowedCategoryTree();

    function displayCategoryTree($categoryTreeRoot, $level, $client, $productPoint, $conn) {
        if($categoryTreeRoot->getCode() != "" && $categoryTreeRoot->isAllowOfferIntegration()) {
            $categoryCode = $categoryTreeRoot->getCode();
            $categoryName = $categoryTreeRoot->getName();
            
            $sql        = "SELECT * FROM tbl_cdiscount_category WHERE categoryCode ='".$categoryCode."'";
            $category   = mysqli_query($conn, $sql);
            if($category->num_rows == 0) {
                $sql    = "INSERT INTO tbl_cdiscount_category SET categoryCode='".$categoryCode."', categoryName='".$categoryName."'"; 
                echo $sql."<br>";
                mysqli_query($conn, $sql);
                
            }
        }
        /** @var \Sdk\Product\CategoryTree $catTree */
        foreach ($categoryTreeRoot->getChildrenCategoryList() as $catTree) {
            displayCategoryTree($catTree, (int)$level + 1, $client, $productPoint, $conn);
        }
    }

    if ($getAllowedCategoryTreeResponse->hasError()) {
        echo "Error : " . $getAllowedCategoryTreeResponse->getErrorMessage();
    }
    else {
        $categoryTreeRoot = $getAllowedCategoryTreeResponse->getRootCategoryTree();
        /**
         * Display category tree
         */
        displayCategoryTree($categoryTreeRoot, 0, $client, $productPoint, $conn);
    }
    echo "end";
?>