<h1 align="center">
	<img src="https://github.com/orisai/.github/blob/main/images/repo_title.png?raw=true" alt="Orisai"/>
	<br/>
	Object Mapper
</h1>

<p align="center">
    Raw data mapping to validated objects
</p>

<p align="center">
	📄 Check out our <a href="docs/README.md">documentation</a>.
</p>

<p align="center">
	💸 If you like Orisai, please <a href="https://orisai.dev/sponsor">make a donation</a>. Thank you!
</p>

<p align="center">
	<a href="https://github.com/orisai/object-mapper/actions?query=workflow%3Aci">
		<img src="https://github.com/orisai/object-mapper/workflows/ci/badge.svg">
	</a>
	<a href="https://coveralls.io/r/orisai/object-mapper">
		<img src="https://badgen.net/coveralls/c/github/orisai/object-mapper/v1.x?cache=300">
	</a>
	<a href="https://dashboard.stryker-mutator.io/reports/github.com/orisai/object-mapper/v1.x">
		<img src="https://badge.stryker-mutator.io/github.com/orisai/object-mapper/v1.x">
	</a>
	<a href="https://packagist.org/packages/orisai/object-mapper">
		<img src="https://badgen.net/packagist/dt/orisai/object-mapper?cache=3600">
	</a>
	<a href="https://packagist.org/packages/orisai/object-mapper">
		<img src="https://badgen.net/packagist/v/orisai/object-mapper?cache=3600">
	</a>
	<a href="https://choosealicense.com/licenses/mpl-2.0/">
		<img src="https://badgen.net/badge/license/MPL-2.0/blue?cache=3600">
	</a>
<p>

##

```php
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;

final class UserInput extends MappedObject
{

	/** @StringValue(notEmpty=true) */
	public string $firstName;

	/** @StringValue(notEmpty=true) */
	public string $lastName;

	/** @MappedObjectValue(UserAddressInput::class) */
	public UserAddressInput $address;

}
```

```php
use Orisai\ObjectMapper\MappedObject;

final class UserAddressInput extends MappedObject
{
	// ...
}
```

```php
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Processing\DefaultProcessor;

$processor = new DefaultProcessor(...);
$errorPrinter = new ErrorVisualPrinter();

$data = [
	'firstName' => '',
	'lastName' => '',
	'address' => [],
];

try {
	$user = $processor->process($data, UserInput::class);
} catch (InvalidData $exception) {
	$error = $errorPrinter->printError($exception);
	throw new Exception("Validation failed due to following error:\n$error");
}
```
