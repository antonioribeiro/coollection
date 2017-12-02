#!/usr/bin/env bash

##
 # Include dotfiles on file operations
 #
shopt -s dotglob

##
 # App
 #
function main()
{
    echo "-- Upgrading Collect..."

    prepareEnvironment

    displayVariables

    createDir

    download

    extract

    renameNamespace

    copyTests

    cleanupDir
}

##
 # Display all variables
 #
function displayVariables()
{
    echo
    echo "-- Variables"
    echo "---------------------------------------------"

    echo collectDir = ${collectDir}
    echo collectVersion = ${collectVersion}
    echo repositoryDir = ${repositoryDir}
    echo collectZip = ${collectZip}
    echo srcDir = ${srcDir}
    echo oldNamespace = ${oldNamespace}
    echo newNamespace = ${newNamespace}

    echo "---------------------------------------------"
}

##
 # Clean the destination directory
 #
function createDir()
{
    if [ -d ${baseDir} ]; then
        echo "-- Cleaning ${baseDir}"

        rm -rf ${baseDir}
    fi

    mkdir -p ${collectDir}
}

##
 # Download a new version
 #
function download()
{
    echo "-- Downloading ${collectZipUrl} to ${collectDir}"

    wget ${collectZipUrl} -O ${collectZip} >/dev/null 2>&1
}

##
 # Extract from compressed file
 #
function extract()
{
    echo "-- Extracting collect.zip..."

    unzip ${collectZip} -d ${collectDir} >/dev/null 2>&1

    rm ${collectZip}

    mv ${repositoryDir}/* ${collectDir}/

    rmdir ${repositoryDir}
}

##
 # Clenup Tightenco/Collect
 #
function cleanupDir()
{
    echo "-- Cleaning up Tightenco dir..."

    mv ${collectDir}/src ${collectDir}/../

    rm -rf ${collectDir}/*

    mv ${collectDir}/../src ${collectDir}/
}

##
 # Copy tests to our tests dir
 #
function getCurrentCollectVersion()
{
    collectVersion=$(git ls-remote https://github.com/tightenco/collect.git | grep tags/ | grep -v {} | cut -d \/ -f 3 | cut -d v -f 2 | sort --version-sort | tail -1)

    echo Upgrading to tightenco/collect $collectVersion
}

##
 # Copy tests to our tests dir
 #
function copyTests()
{
    echo "-- Copying tests..."

    cp ${collectDir}/tests/Support/SupportCollectionTest.php ${homeDir}/tests/
}

##
 # Rename namespace on all files
 #
function renameNamespace()
{
    echo "-- Renaming namespace from Illuminate to Tightenco..."

    mkdir -p ${tightencoNamespaceDir}

    mv ${illuminateNamespaceDir}/* ${tightencoNamespaceDir}/

    rmdir ${illuminateNamespaceDir}

    find ${baseDir} -name "*.php" -exec sed -i "" -e "s|${oldNamespace}|${newNamespace}|g" {} \;
}

function prepareEnvironment()
{
    getCurrentCollectVersion

    ##
     # Define all variables
     #
    homeDir=.
    baseDir=src/vendor/tightenco
    oldNamespace='Illuminate\\'
    newNamespace='Tightenco\\Collect\\'

    collectDir=${baseDir}/collect
    repositoryDir=${collectDir}/collect-${collectVersion}
    collectZip=${collectDir}/collect.zip
    collectZipUrl=https://github.com/tightenco/collect/archive/v${collectVersion}.zip
    srcDir=${collectDir}/src
    tightencoNamespaceDir=${srcDir}/Tightenco/Collect
    illuminateNamespaceDir=${srcDir}/Illuminate
}

##
 # Run the app
 #
main $@
