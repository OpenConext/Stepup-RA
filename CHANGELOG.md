# Changelog

## 3.1.2
**Bugfix**
 * Repair the token export feature #234 
 
## 3.1.1
**Bugfix**
 * Remove filter from ra candidate search form #233

## 3.1.0
A release with bugfixes after initial FGA test:
 * Update translations #228
 * Improve button-group button alignment #231
 * Fix exception controller authentication exceptions #232
 * Remove unused select_raa code #230
 * Security updates #229
 * Test php 7.3 with travis #224

## 3.0.1
This is a security release that will harden the application against CVE 2019-346
 * Upgrade Stepup-saml-bundle to version 4.1.8 

## 3.0.0 FGA (fine grained authorization)

The new fine grained authorization logic will allow Ra's from other institutions to accredidate RA's on behalf of another organisation. This is determined based on the institution configuration. https://github.com/OpenConext/Stepup-Deploy/wiki/rfc-fine-grained-authorization/b6852587baee698cccae7ebc922f29552420a296

**Features & Bugfixes**
The changes to RA in regards to the FGA changes only where to remain compatible with API changes made for Stepup-RA. No new features have been added.

## 2.10.8
This is a security release that will harden the application against CVE 2019-346
 * Upgrade Stepup-saml-bundle to version 4.1.8 

# 2.10.7
**Features**
* Allow filtering the RA candidate on institute #201
* Add search support on the RA management page #200

**Improvements**
* Widen the width of the menu #199
* Remove RAA switcher #198

## 2.10.6
**Improvement**
* Align buttons on 'Tokens' page #194

## 2.10.5
**Bugfix**
* Fix the token sorting PART II #192

## 2.10.4
**Improvements**
* Open help in new tab #187 
* Introduce multi-lingual logout redirect #186 
 
## 2.10.3
**Bugfixes**
* Flip value/label in choice form type definitions #168

**Improvments for testing**
* Ensure middleware API is used in test mode #167

## 2.10.2
**Bugfixes**
* Fix setAllowedTypes usage in VerifyPhoneNumberType form #166

## 2.10.1
* Restore DataExporter repository (thanks @tvdijen for issue 164)

## 2.10.0
**Features & Bugfixes**
* Improved the AccessDenied error page #159
* Fixed missing translations for validation messages on forms #163

**Improvements**
* Symfony 3.4.15 upgrade #162
* Behat test support #160
* Removed RMT from the project
