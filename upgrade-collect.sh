#!/usr/bin/env bash

##
 # Include dotfiles on file operations
 #
shopt -s dotglob

##
 # Define all variables
 #
collectVersion=5.5.16
homeDir=.
baseDir=src/package/Support/Tightenco
oldNamespace='Illuminate\\'
newNamespace='Tightenco\\Collect\\'

collectDir=${baseDir}/Collect
repositoryDir=${collectDir}/collect-5.5.16
collectZip=${collectDir}/collect.zip
srcDir=${collectDir}/src
tightencoNamespaceDir=${srcDir}/Tightenco/Collect
illuminateNamespaceDir=${srcDir}/Illuminate

##
 # App
 #
function main()
{
    echo "Upgrading Collect..."

    # displayVariables

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
    echo "Variables"
    echo "---------"

    echo collectDir = ${collectDir}
    echo collectVersion = ${collectVersion}
    echo repositoryDir = ${repositoryDir}
    echo collectZip = ${collectZip}
    echo srcDir = ${srcDir}
    echo oldNamespace = ${oldNamespace}
    echo newNamespace = ${newNamespace}
}

##
 # Clean the destination directory
 #
function createDir()
{
    if [ -d ${baseDir} ]; then
        echo "Cleaning ${baseDir}"

        rm -rf ${baseDir}
    fi

    mkdir -p ${collectDir}
}

##
 # Download a new version
 #
function download()
{
    FILE=https://github.com/tightenco/collect/archive/v${collectVersion}.zip

    echo "Downloading $FILE to ${collectDir}"

    wget $FILE -O ${collectZip} >/dev/null 2>&1
}

##
 # Extract from compressed file
 #
function extract()
{
    echo "Extracting collect.zip..."

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
    echo "Cleaning up Tightenco..."

    rm -rf ${collectDir}/tests
    rm ${collectDir}/phpunit.xml
}


##
 # Copy tests to our tests dir
 #
function copyTests()
{
    echo "Copying tests..."

    cp ${collectDir}/tests/Support/SupportCollectionTest.php ${homeDir}/tests/
}

##
 # Rename namespace on all files
 #
function renameNamespace()
{
    echo "Renaming namespace from Illuminate to Tightenco..."

    mkdir -p ${tightencoNamespaceDir}

    mv ${illuminateNamespaceDir}/* ${tightencoNamespaceDir}/

    rmdir ${illuminateNamespaceDir}

    find ${baseDir} -name "*.php" -exec sed -i "" -e "s|${oldNamespace}|${newNamespace}|g" {} \;
}

##
 # Run the app
 #
main $@
