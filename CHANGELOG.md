# 8.7.1

## FEATURE
- [FEATURE] Add Github Actions configuration 583fa76

## TASK
- [TASK] Add bk2k/extension-helper 2a9d732
- [TASK] Set new documentation URL 0142ebb
- [TASK] Update Settings.cfg 16547ac
- [TASK] Update URLs to Github a4c8634
- Revert "[TASK] Adjust test to allow for phpunit v8" fe1db23
- [TASK] Adjust test to allow for phpunit v8 a1f959c
- [TASK] bamboo: db dependency loop needs break condition 8a8e9e7
- [TASK] Remove php7.3 from test execution for now b9298f1
- [TASK] Add php7.3 to test execution 338b9f8
- [TASK] Fix minimal PHP version 47c1170
- [TASK] exclude vendor folder from linter 6284ef5
- [TASK] Remove temp folder from linter 8c7bb29
- [TASK] Remove temp folder from linter 7a2575c
- [TASK] Remove temp folder from linter 0f60987
- [TASK] Fix CGL issues f33582a
- [TASK] New bamboo setup 8a2aad4
- [TASK] Have docker-compose.yml for testing 86b5688
- [TASK] Optimize README.md file ae78d75
- [TASK] Adjust ext_emconf.php with author and version d69912d
- [TASK] Adjust Readme and composer.json for release 901a886

## BUGFIX
- [BUGFIX] Change TYPO3 requirement to typo3/cms-core 17e5a62

## MISC
- Update README.md 9282942

# 8.7

## BREAKING
- [!!!][TASK] Make it work with 8.3x 40837d0

## FEATURE
- [FEATURE] Add support for date fields c87e927

## TASK
- [TASK] Test composer branch aliases d6ce771
- [TASK] Test composer branch aliases f4d667b
- [TASK] Test composer branch aliases d485d6e
- [TASK] Remove unneeded tests 3e934cc
- [TASK] Remove unnecessary tests and implement proper ebca5be
- [TASK] Add language file depending on backend language ed8e6be
- [TASK] Add language files 17c7045
- [TASK] Change variable name 00b29c7
- [TASK] Implement more multiple tests with each rule type as input 1b82735
- [TASK] Implement new test with multiple rules and groups 329c6da
- [WIP][TASK] Remove unnecessary code, switch optimization 9e504cb
- [TASK] Add missing escape tests for begin and not-begin dataproviders 8baef9d
- [TASK] Implement not null test dataprovider 38b61fb
- [TASK] Implement null test dataprovider df5f02b
- [TASK] Syntax corrections 7f9162c
- [TASK] Add NOT 2cdf360
- [TASK] Syntax correction 9d4c6f5
- [TASK] Add escape tests for not ends dataprovider b5ba6dc
- [TASK] Implement not_end test dataprovider 4d07a91
- [TASK] Add escape tests to ends dataprovider 0f1e44f
- [TASK] Implement ends test dataprovider f8a1755
- [TASK] Add escape tests to not contains dataprovider 3166fcf
- [TASK] Implement not contains test dataprovider 9369d9d
- [TASK] Implement contains test dataprovider 2426af8
- [TASK] Implement not_end test dataprovider 076f00b
- [TASK] Implement ends test dataprovider de9a675
- [TASK] Implement not contains test dataprovider 9883336
- [TASK] Split string for IN operator by multiple characters 9d48e33
- [TASK] Add not begins test dataprovider dad5bef
- [TASK] Add begins with test dataprovider 26a4c77
- [TASK] Fix test failures f4900a5
- [TASK] Implement in function dataprovider 2e202b8
- [TASK] In operator seperator change and dataprovider adaption 900829f
- [TASK] Test adaption for in new seperator 7ffe573
- [TASK] Change input seperator for in and not_in e41ef35
- [TASK] Add double values to in dataprovider 7aee2d3
- [TASK] Implement in test dataprovider 859de22
- [TASK] Implement dataprovider for not_equal tests d88e16f
- [TASK] Fix equal tests and comma changes in others 28e655b
- [WIP][TASK] Further double values implementation for equal dataprovider 0f808fc
- [TASK] Implement double values for equal test b7bf2ca
- [WIP][TASK] Implement equal test dataprovider 6d6f78c
- [TASK] Remove timesec and year formats as they are not supported by js 144b4ff
- [TASK] Add FunctionalTests.xml cb50654
- [TASK] Correct overrride function recent selected query 34ebe32
- [TASK] Remove unnecessary boolean tests b068d84
- [TASK] Querybuilder adaptions and test preparations 6ea5e54
- [TASK] Finishing tests corrections 89cc718
- [TASK] Implement data for between functions, optimize parser 7d833ce
- [TASK] Between tests adaption e7bdb03
- [TASK] Between functions adaptions a2f502a
- [TASK] Between function dataprovider implementation a2488de
- [TASK] Correct between behaviour and quoting 2c53a79
- [TASK] Adapt test data/expectedresult and add $type cbad9af
- [TASK] General corrections and css changes 6a74e4d
- [TASK] Add further translation ac10342
- [TASK] Working translation implementation 89d2f77
- [TASK] Add more data f946819
- [TASK] Further test and parser adaptions 8a2b3d8
- [TASK] Dataprovider type implementation ab1563c
- [TASK] Refactor to createNamedParameter f682af4
- [TASK] Add support for field type number 5c1b83a
- [TASK] Add support for field type number 471533a
- [TASK] Add support for filter type time 87d1b02
- [TASK] Add support for filter type integer e15e7e9
- [TASK] Add more tests f7f1489
- [TASK] Tests adaption, translation implementation 4f2be1b
- [TASK] Fix some functional tests c54ec10
- [TASK] Fix some tests and rename one variable b036218
- [TASK] Functionaltests buildup ab00ce2
- [TASK] Add first functional tests 31ff1a3
- [TASK] Cleanup code 8f4e001
- [TASK] Enable between and empty operators b699288
- [TASK] Adapt "empty"-operator d23734e
- [TASK] jQuery Update, "in"-operator implementation 34aebab
- [TASK] Remove unused jQuery select calls a34d620
- [TASK] Update dropdown for saved queries after update a074fe0
- [TASK] Optimize result handling c2e70db
- [TASK] Disable checkbox for override if no query is selected 8153e87
- [TASK] Add errorhandling for empty recent query e32147c
- [TASK] Override function adaptions ef6271f
- [TASK] Optimize override function 3c0a4eb
- [TASK] Controller core adaptions ea4eb57
- [TASK] Improve save query functionality bc3c79f
- [TASK] Optimize controller ced64e6
- [TASK] Allow override of query and queryname 8e8f421
- [TASK] Add validation for queryname on save 22ecd9b
- [TASK] Get tablename from URL if table is not available be9f9b2
- [TASK] Adapt override function setup f83b9ae
- [TASK] Adapt override checkbox functionality 304d2b9
- [TASK] Remove private implementation in JS and PHP a8fc947
- [TASK] Only show recent queries for user 492736d
- [TASK] Add icon for querybuilder table f630754
- [TASK] Add hidden field fb95f0f
- [TASK] First private Query implementation b4a3df0
- [TASK]  Load query e3ccc6d
- [TASK] Add data to dropdown 1d1439e
- [TASK] Ajax implementation for recent queries 747afa5
- [TASK] Further dropdown implementation and adaption 280744c
- [TASK] Implement dropdown for save queries ccc7402
- [TASK] Adapt keyup function 4cb186f
- [TASK] Implement save 2d2ebb2
- [TASK] Cleanup code 0a3ce2a
- [TASK] Optimize modal content f087f0f
- [TASK] Ajax Setup for query saving c0c6ef0
- [TASK] Cleanup JS 5a418bd
- [TASK] Use local storage to store last query 5663468
- [TASK] Fix wrong namespaces a50d249
- [TASK] Ajax startup and first implementation c400e7b
- [TASK] Implement dummy query safe function 93157c9
- [TASK] Startup for safe query function, create table db89123
- [TASK] Code optimization 1f523ca
- [TASK] Remove debug comment 8c1cce0
- [TASK] Remove unneeded operators bbdbd24
- [TASK] Fix type declaration 042f782
- [TASK] Add strict declaration and return types e273d2b
- [TASK] Enable query request by pressing enter in operator and input field ecfde5f
- [TASK] Code cleanup and small fixes f568724
- [TASK] Block empty request query-wise 9daf978
- [TASK] Further reset button implementation 1468054
- [TASK] Unneeded Code removal and general adaptions 2fe0599
- [TASK] Remove unneeded code and 'where' in builder 1b9b49b
- [TASK] Zwischenstand 6f26d4a
- [TASK] Further whereclause adaption 6596791
- [TASK] Adapt database connection to doctrine cee34f0
- [TASK] Upgrade QueryBuilder js files fa07d6a
- [TASK] adjust typo3/cms version constraint ab6f41e
- [TASK] Adjust composer dependency to typo3/cms ebc4b21
- [!!!][TASK] Make it work with 8.3x 40837d0
- [TASK] Cleanup: Move logic into own classes 467ec9e
- [TASK] Add filter dynamic from TCA 7140fda
- [TASK] Style QueryBuilder c743094
- [TASK] Add grouping for queries 40e883b
- [TASK] First commit 02ac417
- [TASK] template with README and .gitignore 29ee6cc
- [TASK] Initialize Repository 6783304

## BUGFIX
- [BUGFIX] Adapt dataproviders for new double handling d16d4f4
- [BUGFIX] Fix wrong double input handling 6fca3b8
- [BUGFIX] Fix conversion of datetime, date and time fields 9ba99de
- [BUGFIX] Add escaping for like values bb1cd12
- [BUGFIX] Add Tests to autoload 78361d9
- [BUGFIX] Correct syntax, remove unneeded code 95f47d1
- [BUGFIX] Fix save and override query function add7200
- [BUGFIX] Fix checkbox input array quoting 868cf98
- [BUGFIX] Change check for type 5deffd5
- [BUGFIX] Fix between f02eabb
- [BUGFIX] Fix JS error for checkbox 46319bb
- [BUGFIX] Readd initializeEvents 5aca6a4
- [BUGFIX] Reset local storage for current table on „reset“ 797ec40
- [BUGFIX] Remove EvaluateDisplayConditions from DataProvider 7097510
- [BUGFIX] Change DateProviderGroup for 8.6 dd491af
- [BUGFIX] Check if column declration exists 6e2c31a
- [BUGFIX] Fix wrong path in autoloader config b14ba3e
- [BUGFIX] Fix incompatible type 8962daa
- [BUGFIX] Fix init of QueryBuilder 4eefe23

## MISC
- [CLEANUP] Correct constant typo c316def
- [CLEANUP] Small optimizations 286ccd4
- [CLEANUP] First small code cleanup 05cca7c

