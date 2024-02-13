<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Class Surfnet\\\\NoDebugFunctionRule implements generic interface PHPStan\\\\Rules\\\\Rule but does not specify its types\\: TNodeType$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/NoDebugFunctionRule.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Assert\\:\\:keysAre\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Assert.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Assert\\:\\:keysAre\\(\\) has parameter \\$expectedKeys with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Assert.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Assert\\:\\:keysAre\\(\\) has parameter \\$propertyPath with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Assert.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\AccreditCandidateCommand\\:\\:\\$availableInstitutions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/AccreditCandidateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaCandidatesCommand\\:\\:\\$institutionFilterOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/SearchRaCandidatesCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaListingCommand\\:\\:\\$institutionFilterOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/SearchRaListingCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaListingCommand\\:\\:\\$raInstitutionFilterOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/SearchRaListingCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaSecondFactorsCommand\\:\\:\\$institutionFilterOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/SearchRaSecondFactorsCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRecoveryTokensCommand\\:\\:\\$institutionFilterOptions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/SearchRecoveryTokensCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SelectInstitutionCommand\\:\\:\\$availableInstitutions type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/SelectInstitutionCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\VettingTypeHintCommand\\:\\:__get\\(\\) has parameter \\$name with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/VettingTypeHintCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\VettingTypeHintCommand\\:\\:__isset\\(\\) has parameter \\$name with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/VettingTypeHintCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\VettingTypeHintCommand\\:\\:__set\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/VettingTypeHintCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\VettingTypeHintCommand\\:\\:assertValidLanguageInName\\(\\) has parameter \\$name with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/VettingTypeHintCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\VettingTypeHintCommand\\:\\:setHints\\(\\) has parameter \\$hints with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Command/VettingTypeHintCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\ExceptionController\\:\\:getPageTitleAndDescription\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/ExceptionController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserProviderInterface\\:\\:switchLocale\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/LocaleController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getToken\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/LocaleController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\LocaleController\\:\\:__construct\\(\\) has parameter \\$identityService with generic interface Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserProviderInterface but does not specify its types\\: TUser$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/LocaleController.php',
];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/LocaleController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$url of method Symfony\\\\Bundle\\\\FrameworkBundle\\\\Controller\\\\AbstractController\\:\\:redirect\\(\\) expects string, bool\\|float\\|int\\|string\\|null given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/LocaleController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupBundle\\\\Command\\\\SwitchLocaleCommand\\:\\:\\$identityId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/LocaleController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/ProfileController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getRaaInstitutions\\(\\) on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\Profile\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getToken\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$id of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\RaLocationService\\:\\:find\\(\\) expects string, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$identityId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\ProfileService\\:\\:findByIdentityId\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\.\\.\\.\\$values of function sprintf expects bool\\|float\\|int\\|string\\|null, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\ChangeRaLocationCommand\\:\\:\\$currentUserId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\CreateRaLocationCommand\\:\\:\\$currentUserId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\CreateRaLocationCommand\\:\\:\\$institution \\(string\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\RemoveRaLocationCommand\\:\\:\\$currentUserId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaLocationsCommand\\:\\:\\$orderBy \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaLocationsCommand\\:\\:\\$orderDirection \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaLocationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaCandidatesCommand\\:\\:\\$actorInstitution\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Symfony\\\\Component\\\\Form\\\\FormInterface\\:\\:isClicked\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserInterface\\|null\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$institution on Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserInterface\\|null\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$institutions on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\RaCandidateInstitutions\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$raCandidate on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\RaCandidateInstitutions\\|null\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getToken\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot cast mixed to int\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\RaManagementController\\:\\:amendRaInformation\\(\\) has parameter \\$identityId with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\RaManagementController\\:\\:amendRaInformation\\(\\) has parameter \\$raInstitution with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\RaManagementController\\:\\:retractRegistrationAuthority\\(\\) has parameter \\$identityId with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\RaManagementController\\:\\:retractRegistrationAuthority\\(\\) has parameter \\$raInstitution with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$identityId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\RaCandidateService\\:\\:getRaCandidate\\(\\) expects string, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\.\\.\\.\\$values of function sprintf expects bool\\|float\\|int\\|string\\|null, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\AccreditCandidateCommand\\:\\:\\$identityId \\(string\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaCandidatesCommand\\:\\:\\$actorId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaCandidatesCommand\\:\\:\\$orderBy \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaCandidatesCommand\\:\\:\\$orderDirection \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaListingCommand\\:\\:\\$actorId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaListingCommand\\:\\:\\$orderBy \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaListingCommand\\:\\:\\$orderDirection \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaManagementController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getRaaInstitutions\\(\\) on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\Profile\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaaController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$identityId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\ProfileService\\:\\:findByIdentityId\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RaaController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getUser\\(\\) on Symfony\\\\Component\\\\Security\\\\Core\\\\Authentication\\\\Token\\\\TokenInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RecoveryTokenController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot cast mixed to int\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RecoveryTokenController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\RevokeRecoveryTokenCommand\\:\\:\\$currentUserId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RecoveryTokenController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRecoveryTokensCommand\\:\\:\\$actorId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RecoveryTokenController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRecoveryTokensCommand\\:\\:\\$orderBy \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RecoveryTokenController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRecoveryTokensCommand\\:\\:\\$orderDirection \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/RecoveryTokenController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Symfony\\\\Component\\\\Form\\\\FormInterface\\:\\:getClickedButton\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserProviderInterface\\:\\:findById\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getToken\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot cast mixed to int\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\SecondFactorController\\:\\:__construct\\(\\) has parameter \\$identityService with generic interface Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserProviderInterface but does not specify its types\\: TUser$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\.\\.\\.\\$values of function sprintf expects bool\\|float\\|int\\|string\\|null, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\.\\.\\.\\$values of function sprintf expects bool\\|float\\|int\\|string\\|null, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\RevokeSecondFactorCommand\\:\\:\\$currentUserId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaSecondFactorsCommand\\:\\:\\$actorId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaSecondFactorsCommand\\:\\:\\$orderBy \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchRaSecondFactorsCommand\\:\\:\\$orderDirection \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchSecondFactorAuditLogCommand\\:\\:\\$orderBy \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\SearchSecondFactorAuditLogCommand\\:\\:\\$orderDirection \\(string\\|null\\) does not accept mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/SecondFactorController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Surfnet\\\\StepupBundle\\\\Value\\\\Provider\\\\ViewConfigInterface\\:\\:getInitiate\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/Vetting/Gssf/GssfInitiateFormService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\Vetting\\\\Gssf\\\\GssfInitiateFormService\\:\\:renderInitiateForm\\(\\) has parameter \\$parameters with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/Vetting/Gssf/GssfInitiateFormService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$gssfId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\VettingService\\:\\:verifyGssfId\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/Vetting/Gssf/GssfVerifyController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Psr\\\\Container\\\\ContainerInterface\\:\\:getParameter\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method forProcedure\\(\\) on mixed\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getToken\\(\\) on mixed\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Controller\\\\VettingController\\:\\:cancelProcedure\\(\\) has parameter \\$procedureId with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$actorId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\SecondFactorService\\:\\:findVerifiedSecondFactorByRegistrationCode\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\StartVettingProcedureCommand\\:\\:\\$authorityId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingController.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getRaaInstitutions\\(\\) on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\Profile\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingTypeHintController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$identityId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\ProfileService\\:\\:findByIdentityId\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingTypeHintController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Command\\\\VettingTypeHintCommand\\:\\:\\$identityId \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Controller/VettingTypeHintController.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property Surfnet\\\\StepupRa\\\\RaBundle\\\\DateTime\\\\DateTime\\:\\:\\$now is never written, only read\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/DateTime/DateTime.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Symfony\\\\Component\\\\Config\\\\Definition\\\\Builder\\\\NodeDefinition\\:\\:children\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/DependencyInjection/Configuration.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method integerNode\\(\\) on Symfony\\\\Component\\\\Config\\\\Definition\\\\Builder\\\\NodeParentInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/DependencyInjection/Configuration.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Symfony\\\\Contracts\\\\Translation\\\\TranslatorInterface\\:\\:setLocale\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/EventListener/LocaleListener.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:__construct\\(\\) has parameter \\$code with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:__construct\\(\\) has parameter \\$constraints with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:__construct\\(\\) has parameter \\$message with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:__construct\\(\\) has parameter \\$propertyPath with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:__construct\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:getConstraints\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:getPropertyPath\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Exception\\\\AssertionFailedException\\:\\:getValue\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Exception/AssertionFailedException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Form\\\\Extension\\\\RaRoleChoiceList\\:\\:buildChoices\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Extension/RaRoleChoiceList.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Form\\\\Extension\\\\RaRoleChoiceList\\:\\:create\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Extension/RaRoleChoiceList.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Form\\\\Extension\\\\RaRoleChoiceList\\:\\:getChoices\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Extension/RaRoleChoiceList.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Form\\\\Extension\\\\SecondFactorTypeChoiceList\\:\\:create\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Extension/SecondFactorTypeChoiceList.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$availableInstitutions on mixed\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Type/CreateRaType.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$institutionFilterOptions on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Type/SearchRaCandidatesType.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @var has invalid value \\(\\$command SearchRaCandidatesCommand\\)\\: Unexpected token "\\$command", expected type at offset 9$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Type/SearchRaCandidatesType.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$availableInstitutions on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Type/SelectInstitutionType.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$locales on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Form/Type/VettingTypeHintType.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Logger\\\\ProcedureAwareLogger\\:\\:enrichContext\\(\\) has parameter \\$context with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Logger/ProcedureAwareLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Logger\\\\ProcedureAwareLogger\\:\\:enrichContext\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Logger/ProcedureAwareLogger.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Repository\\\\SessionVettingProcedureRepository\\:\\:retrieve\\(\\) should return Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Repository/SessionVettingProcedureRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Repository\\\\VettingProcedureRepository\\:\\:store\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Repository/VettingProcedureRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\AuthenticatedIdentity\\:\\:eraseCredentials\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/AuthenticatedIdentity.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\AuthenticatedSessionStateHandler\\:\\:setCurrentRequestUri\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/AuthenticatedSessionStateHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method logout\\(\\) on an unknown class Symfony\\\\Component\\\\Security\\\\Http\\\\Logout\\\\CookieClearingLogoutHandler\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/ExplicitSessionTimeoutHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method logout\\(\\) on an unknown class Symfony\\\\Component\\\\Security\\\\Http\\\\Logout\\\\SessionLogoutHandler\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/ExplicitSessionTimeoutHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Symfony\\\\Component\\\\Security\\\\Core\\\\Authentication\\\\Token\\\\Storage\\\\TokenStorageInterface\\:\\:setToken\\(\\) invoked with 0 parameters, 1 required\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/ExplicitSessionTimeoutHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$cookieClearingLogoutHandler of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Handler\\\\ExplicitSessionTimeoutHandler\\:\\:__construct\\(\\) has invalid type Symfony\\\\Component\\\\Security\\\\Http\\\\Logout\\\\CookieClearingLogoutHandler\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/ExplicitSessionTimeoutHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$sessionLogoutHandler of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Handler\\\\ExplicitSessionTimeoutHandler\\:\\:__construct\\(\\) has invalid type Symfony\\\\Component\\\\Security\\\\Http\\\\Logout\\\\SessionLogoutHandler\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/ExplicitSessionTimeoutHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Handler\\\\ExplicitSessionTimeoutHandler\\:\\:\\$cookieClearingLogoutHandler has unknown class Symfony\\\\Component\\\\Security\\\\Http\\\\Logout\\\\CookieClearingLogoutHandler as its type\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/ExplicitSessionTimeoutHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Handler\\\\ExplicitSessionTimeoutHandler\\:\\:\\$sessionLogoutHandler has unknown class Symfony\\\\Component\\\\Security\\\\Http\\\\Logout\\\\SessionLogoutHandler as its type\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/ExplicitSessionTimeoutHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$requestId of method Surfnet\\\\SamlBundle\\\\Monolog\\\\SamlAuthenticationLogger\\:\\:forAuthentication\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/InitiateSamlAuthenticationHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$preferredLocale on Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/LogoutSuccessHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getUser\\(\\) on Symfony\\\\Component\\\\Security\\\\Core\\\\Authentication\\\\Token\\\\TokenInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Handler/LogoutSuccessHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$authorizations on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\Profile\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$isSraa on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\Profile\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Class Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Provider\\\\SamlProvider implements generic interface Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserProviderInterface but does not specify its types\\: TUser$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Provider\\\\SamlProvider\\:\\:getNameId\\(\\) should return string but returns string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$array of function reset expects array\\|object, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$identityId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\ProfileService\\:\\:findByIdentityId\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$loa of class Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Token\\\\SamlToken constructor expects Surfnet\\\\StepupBundle\\\\Value\\\\Loa, Surfnet\\\\StepupBundle\\\\Value\\\\Loa\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$loaIdentifier of method Surfnet\\\\StepupBundle\\\\Service\\\\LoaResolutionService\\:\\:getLoa\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$nameId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\IdentityService\\:\\:findByNameIdAndInstitution\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value of function count expects array\\|Countable, mixed given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Provider/SamlProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\SamlAuthenticationStateHandler\\:\\:setRequestId\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/SamlAuthenticationStateHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\SamlInteractionProvider\\:\\:\\$loaResolutionService is never read, only written\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/SamlInteractionProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Unreachable statement \\- code above always terminates\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/SamlInteractionProvider.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Session\\\\SessionStorage\\:\\:getCurrentRequestUri\\(\\) should return string but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Session/SessionStorage.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Session\\\\SessionStorage\\:\\:getRequestId\\(\\) should return string\\|null but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Session/SessionStorage.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$string of static method Surfnet\\\\StepupRa\\\\RaBundle\\\\Value\\\\DateTime\\:\\:fromString\\(\\) expects string, mixed given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Session/SessionStorage.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Authentication\\\\Token\\\\SamlToken\\:\\:__unserialize\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Authentication/Token/SamlToken.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Factory\\\\SamlFactory\\:\\:create\\(\\) has parameter \\$config with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Factory/SamlFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Factory\\\\SamlFactory\\:\\:create\\(\\) has parameter \\$defaultEntryPoint with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Factory/SamlFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Factory\\\\SamlFactory\\:\\:create\\(\\) has parameter \\$userProvider with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Factory/SamlFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Factory\\\\SamlFactory\\:\\:create\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Factory/SamlFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Security\\\\Factory\\\\SamlFactory\\:\\:createAuthenticator\\(\\) should return array\\<string\\>\\|string but returns array\\<string, mixed\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Security/Factory/SamlFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$orderBy of method Surfnet\\\\StepupMiddlewareClient\\\\Identity\\\\Dto\\\\SecondFactorAuditLogSearchQuery\\:\\:setOrderBy\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/AuditLogService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getUser\\(\\) on Symfony\\\\Component\\\\Security\\\\Core\\\\Authentication\\\\Token\\\\TokenInterface\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/CommandService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'gssfId\' on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/GssfService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'procedureId\' on mixed\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/GssfService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$procedureId of static method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\Gssf\\\\VerificationResult\\:\\:verificationFailed\\(\\) expects string, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/GssfService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$procedureId of static method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\Gssf\\\\VerificationResult\\:\\:verificationSucceeded\\(\\) expects string, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/GssfService.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserInterface\\:\\:getUsername\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/IdentityService.php',
];
$ignoreErrors[] = [
	'message' => '#^Class Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\IdentityService implements generic interface Symfony\\\\Component\\\\Security\\\\Core\\\\User\\\\UserProviderInterface but does not specify its types\\: TUser$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/IdentityService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\IdentityService\\:\\:supportsClass\\(\\) has parameter \\$class with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/IdentityService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\InstitutionConfigurationOptionsServiceInterface\\:\\:getInstitutionConfigurationOptionsFor\\(\\) has parameter \\$institution with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/InstitutionConfigurationOptionsServiceInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\InstitutionListingService\\:\\:getAll\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/InstitutionListingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Command\\\\AccreditIdentityCommand\\:\\:\\$raInstitution \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaCandidateService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Command\\\\AccreditIdentityCommand\\:\\:\\$role \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaCandidateService.php',
];
$ignoreErrors[] = [
	'message' => '#^Left side of && is always true\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaListingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$raInstitution of method Surfnet\\\\StepupMiddlewareClient\\\\Identity\\\\Dto\\\\RaListingSearchQuery\\:\\:setRaInstitution\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaListingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$role of method Surfnet\\\\StepupMiddlewareClient\\\\Identity\\\\Dto\\\\RaListingSearchQuery\\:\\:setRole\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaListingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\RaSecondFactorExport\\:\\:export\\(\\) has parameter \\$fileName with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorExport.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$stream of function fclose expects resource, resource\\|false given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorExport.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$stream of function fflush expects resource, resource\\|false given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorExport.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$stream of function fputcsv expects resource, resource\\|false given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorExport.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\RaSecondFactorService\\:\\:buildQuery\\(\\) has parameter \\$command with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\RaSecondFactorService\\:\\:buildQuery\\(\\) has parameter \\$query with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\RaSecondFactorService\\:\\:search\\(\\) should return Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\RaSecondFactorCollection but returns Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\RaSecondFactorCollection\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$collection of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\RaSecondFactorExport\\:\\:export\\(\\) expects Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\RaSecondFactorExportCollection, Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\RaSecondFactorExportCollection\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaSecondFactorService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Command\\\\AppointRoleCommand\\:\\:\\$role \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/RaService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$haystack of function in_array expects array, array\\|bool\\|float\\|int\\|string\\|UnitEnum\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/SecondFactorAssertionService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getElements\\(\\) on Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\VerifiedSecondFactorCollection\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/SecondFactorService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\SecondFactorService\\:\\:findVerifiedSecondFactorByRegistrationCode\\(\\) should return Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\VerifiedSecondFactor\\|null but returns Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Dto\\\\VerifiedSecondFactor\\|false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/SecondFactorService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getAuthorityId\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getDocumentNumber\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getRegistrationCode\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getSecondFactor\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 17,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getYubikeyPublicId\\(\\) on Surfnet\\\\StepupBundle\\\\Value\\\\YubikeyPublicId\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isIdentityVerified\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method isProvePossessionSkippable\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method verifyIdentity\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method verifySecondFactorIdentifier\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method vet\\(\\) on Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\VettingService\\:\\:getProcedure\\(\\) never returns null so it can be removed from the return type\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$procedureId of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Service\\\\VettingService\\:\\:getProcedure\\(\\) expects string, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$vettingProcedure of method Surfnet\\\\StepupRa\\\\RaBundle\\\\Repository\\\\VettingProcedureRepository\\:\\:store\\(\\) expects Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure, Surfnet\\\\StepupRa\\\\RaBundle\\\\VettingProcedure\\|null given\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Command\\\\VetSecondFactorCommand\\:\\:\\$documentNumber \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Command\\\\VetSecondFactorCommand\\:\\:\\$identityVerified \\(bool\\) does not accept bool\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Command\\\\VetSecondFactorCommand\\:\\:\\$provePossessionSkipped \\(bool\\) does not accept bool\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Surfnet\\\\StepupMiddlewareClientBundle\\\\Identity\\\\Command\\\\VetSecondFactorCommand\\:\\:\\$registrationCode \\(string\\) does not accept string\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/VettingService.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getYubikeyPublicId\\(\\) on Surfnet\\\\StepupBundle\\\\Value\\\\YubikeyPublicId\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Service/YubikeySecondFactorService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\RaBundle\\\\Twig\\\\Extensions\\\\Extension\\\\SecondFactorType\\:\\:translateSecondFactorType\\(\\) has parameter \\$secondFactorType with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Twig/Extensions/Extension/SecondFactorType.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property Surfnet\\\\StepupRa\\\\RaBundle\\\\Value\\\\DateTime\\:\\:\\$now is never written, only read\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/RaBundle/Value/DateTime.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method scalarNode\\(\\) on Symfony\\\\Component\\\\Config\\\\Definition\\\\Builder\\\\NodeParentInterface\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/Configuration.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$rootNode of method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\Configuration\\:\\:addProvidersSection\\(\\) expects Symfony\\\\Component\\\\Config\\\\Definition\\\\Builder\\\\ArrayNodeDefinition, Symfony\\\\Component\\\\Config\\\\Definition\\\\Builder\\\\NodeDefinition given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/Configuration.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$rootNode of method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\Configuration\\:\\:addRoutesSection\\(\\) expects Symfony\\\\Component\\\\Config\\\\Definition\\\\Builder\\\\ArrayNodeDefinition, Symfony\\\\Component\\\\Config\\\\Definition\\\\Builder\\\\NodeDefinition given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/Configuration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:buildHostedEntityDefinition\\(\\) has parameter \\$configuration with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:buildHostedEntityDefinition\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createHostedDefinitions\\(\\) has parameter \\$configuration with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createHostedDefinitions\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createMetadataDefinition\\(\\) has parameter \\$configuration with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createMetadataDefinition\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createRemoteDefinition\\(\\) has parameter \\$configuration with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createRouteConfig\\(\\) has parameter \\$provider with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createRouteConfig\\(\\) has parameter \\$routeName with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:createRouteConfig\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:loadProviderConfiguration\\(\\) has parameter \\$configuration with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:loadProviderConfiguration\\(\\) has parameter \\$provider with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\DependencyInjection\\\\SurfnetStepupRaSamlStepupProviderExtension\\:\\:loadProviderConfiguration\\(\\) has parameter \\$routes with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/DependencyInjection/SurfnetStepupRaSamlStepupProviderExtension.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Exception\\\\InvalidArgumentException\\:\\:invalidType\\(\\) has parameter \\$expectedType with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Exception/InvalidArgumentException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Exception\\\\InvalidArgumentException\\:\\:invalidType\\(\\) has parameter \\$parameter with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Exception/InvalidArgumentException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Exception\\\\InvalidArgumentException\\:\\:invalidType\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Exception/InvalidArgumentException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Exception\\\\UnknownProviderException\\:\\:create\\(\\) has parameter \\$knownProviders with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Exception/UnknownProviderException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Exception\\\\UnknownProviderException\\:\\:create\\(\\) has parameter \\$unknownProvider with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Exception/UnknownProviderException.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$childNodes on DOMElement\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/Metadata.php',
];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always false\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/Metadata.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getLocale\\(\\) on Symfony\\\\Component\\\\HttpFoundation\\\\Request\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:__construct\\(\\) has parameter \\$explanation with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:__construct\\(\\) has parameter \\$gssfIdMismatch with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:__construct\\(\\) has parameter \\$initiate with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:__construct\\(\\) has parameter \\$pageTitle with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:__construct\\(\\) has parameter \\$title with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:getExplanation\\(\\) should return string but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:getGssfIdMismatch\\(\\) should return string but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:getInitiate\\(\\) should return string but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:getPageTitle\\(\\) should return string but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:getTitle\\(\\) should return string but returns mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Provider\\\\ViewConfig\\:\\:getTranslation\\(\\) has parameter \\$translations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Provider/ViewConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Saml\\\\StateHandler\\:\\:get\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Saml/StateHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Saml\\\\StateHandler\\:\\:get\\(\\) has parameter \\$key with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Saml/StateHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Saml\\\\StateHandler\\:\\:set\\(\\) has parameter \\$key with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Saml/StateHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Surfnet\\\\StepupRa\\\\SamlStepupProviderBundle\\\\Saml\\\\StateHandler\\:\\:set\\(\\) has parameter \\$value with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../../src/Surfnet/StepupRa/SamlStepupProviderBundle/Saml/StateHandler.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
