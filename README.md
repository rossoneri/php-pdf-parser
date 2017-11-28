This is a refactor version of  [php-pdf-parser](https://github.com/adeel/php-pdf-parser). The API is basically the same.



    <?php
    
    use rossoneri\pdfparser\Reader;
    
    require_once 'vendor/autoload.php';
    
    $pdf = new Reader(fopen('test.pdf', 'rb'));
    print $pdf->page_count;
    
    ?>
