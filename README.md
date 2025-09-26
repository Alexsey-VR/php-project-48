### FILE DIFFERENCE CALCULATOR

### Hexlet tests and linter status:
[![Actions Status](https://github.com/Alexsey-VR/php-project-48/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/Alexsey-VR/php-project-48/actions) [![check-for-linter](https://github.com/Alexsey-VR/php-project-48/actions/workflows/check-for-linter.yml/badge.svg)](https://github.com/Alexsey-VR/php-project-48/actions/workflows/check-for-linter.yml) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=Alexsey-VR_php-project-48&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=Alexsey-VR_php-project-48)

This is a console application that used to compute differences between two .json or .yaml files.
Output results may be shown to the console in several formats, such that Stylish, Plane or JSON.

To getting start for this application use several steps:
1. install PHP 8.3 and latest version of the Composer framework;
2. install required packages:
$ make install
3. run application:

$ bin/gendiff fixtures/file1.json fixtures/file2.json

To print help to the console use command:

$ bin/gendiff -h

For developers may be used function, that return string result in selected format: 

### List of functions:
* [gendiff(string file1, string file2, string format)](https://github.com/Alexsey-VR/php-project-48/blob/main/docs/gendiff.gif)

