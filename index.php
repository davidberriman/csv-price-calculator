<?php require_once("controllers/index_controller.php"); ?>
<!DOCTYPE html>
<html lang='en-gb'>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CSV Calculator</title>
	<link rel="STYLESHEET" href="css/main.css"/>
</head>
<body>

<h1>Price Calculator - David Berriman</h1>
<p>This is an example of programming by David Berriman. The task brief can be found: <a href="brief.txt">here</a></p>
<p>Click <a href="tests.php">here</a> for the unit test page</p>
<?php  echo isSet($html) ? $html : ""; ?>

<h2>Examples</h2>
<p>The examples below demonstrate the use of the class getters to extract values for the product using product code: <strong><?php echo $code; ?></strong></p>
<ul>
	<li>Price (no tax) for product : <?php echo $priceCalculator->getPriceNoTaxForProductCode($code); ?></li>
	<li>Price (no tax) for product (pence) : <?php echo $priceCalculator->getPriceNoTaxForProductCodeInPence($code); ?></li>
	<li>Price With addition : <?php echo $priceCalculator->getPriceWithAdditionForProductCode($code); ?></li>
	<li>Price With addition (pence): <?php echo $priceCalculator->getPriceWithAdditionForProductCodeInPence($code); ?></li>
	<li>Tax amount: <?php echo $priceCalculator->getTaxRateForProductCode($code); ?>%</li>
	<li>Price (with tax) for product :  <?php echo $priceCalculator->getPriceWithTaxForProductCode($code); ?></li>
	<li>Price (with tax) for product (pence) : <?php echo $priceCalculator->getPriceWithTaxForProductCodeInPence($code); ?></li>
	<li>Price (with Addition and tax) for product :  <?php echo $priceCalculator->getPriceWithAdditionAndTaxForProductCode($code); ?></li>
	<li>Price (with Addition and tax) for product (pence) : <?php echo $priceCalculator->getPriceWithAdditionAndTaxForProductCodeInPence($code); ?></li>
	<li>Conversion of 10.73 to USD: <?php  $conversion = $priceCalculator->convertToCurrency($priceCalculator->getPriceNoTaxForProductCode($code), "USD"); echo ($conversion != false) ? $conversion : $priceCalculator->error; ?></li>
</ul>

<h2>Alternate method </h2>
<p>An alternative method it to use the class array and access the data using the product code as the index and one of the static variables as the id eg: <?php $array = $priceCalculator->getCSVData($code); echo $array[$code]['priceWithTax'] ?></p>

</body>
</html>