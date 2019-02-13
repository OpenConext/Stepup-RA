# Changelog

## 2.10.7
**Features**
* Allow filtering the RA candidate on institute #201 
* Add search support on the RA management page #200

**Improvements**
* Widen the width of the menu #199
* Remove RAA switcher #198

## 2.10.6

## 2.10.5

## 2.10.4
**Improvements**
* Open help in new tab #187 
* Introduce multi-lingual logout redirect #186 

## FGA (fine grained authorization)
**New features**

The new fine grained authorization logic will allow Ra's from other institutions to accredidate RA's on behalf of another organisation.
This is determined based on the institution configuration.
https://github.com/OpenConext/Stepup-Deploy/wiki/rfc-fine-grained-authorization/b6852587baee698cccae7ebc922f29552420a296

* Implement the new FGA feature #169 > # 182

## Develop
**Bugfixes**
* Fix the token sorting #185
 
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
