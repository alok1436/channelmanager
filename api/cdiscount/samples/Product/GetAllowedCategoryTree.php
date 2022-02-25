<?php
    /**
     * Created by guillaume.cochard.
     * Mail: guillaume.cochard@ext.cdiscount.com
     * Date: 20/10/2016
     * Time: 14:42
     */

    require '../../vendor/autoload.php';
    require '../../sdk/autoload.php';
    require '../../../config.php';

error_reporting(-1);

$client = new \Sdk\ApiClient\CDSApiClient();
$token = $client->init();

if ($token == null || !$client->isTokenValid()) {
    echo "Oups, souci lors de la génération du token";
    die;
}

$productPoint = $client->getProductPoint();

/******* GET ALLOWED CATEGORY TREE *******/

$getAllowedCategoryTreeResponse = $productPoint->getAllowedCategoryTree();

function displayCategoryTree($categoryTreeRoot, $level, $client, $productPoint) {

    echo "<br/>";

    $cnt = 0;

    while ($cnt <= $level) {
        echo "....";
        ++$cnt;
    }

    /** @var \Sdk\Product\CategoryTree $categoryTreeRoot */
    echo "Level : " . $level .
        " Code : " . $categoryTreeRoot->getCode() .
        " - Name : " . $categoryTreeRoot->getName() .
        " - AllowOfferIntegration:  " . ($categoryTreeRoot->isAllowOfferIntegration() ? 'true' : 'false') .
        " - AllowProductIntegration:  " . ($categoryTreeRoot->isAllowProductIntegration() ? 'true' : 'false') .
        " - EANOptional:  " . ($categoryTreeRoot->isEanOptional() ? 'true' : 'false') .
        "<br/>";

    if($categoryTreeRoot->getCode() != "") {
        $categoryCode = $categoryTreeRoot->getCode();
        $categoryName = $categoryTreeRoot->getName();
        
        $sql        = "SELECT * FROM tbl_cdiscount_category WHERE categoryCode ='".$categoryCode."'";
        $category   = mysqli_query($conn, $sql);
        if($category->num_rows == 0) {
            $sql    = "INSERT INTO tbl_cdiscount_category SET categoryCode='".$categoryCode."', categoryName='".$categoryName."'"; 
            mysqli_query($conn, $sql);
            echo("Error description: " . mysqli_error($conn));
            echo "<br>";
        }
    }
    /** @var \Sdk\Product\CategoryTree $catTree */
    foreach ($categoryTreeRoot->getChildrenCategoryList() as $catTree) {
        displayCategoryTree($catTree, (int)$level + 1, $client, $productPoint);
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
    displayCategoryTree($categoryTreeRoot, 0, $client, $productPoint);
}
