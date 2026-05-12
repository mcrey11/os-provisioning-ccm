#!/bin/bash

# This script is called from PHP to create PDF files using pdflatex.
# The original approach (calling pdflatex directly from PHP) caused some problems
# related to SettlementRuns (combining commands and trying to get return code)


# define binaries to be executed
CMD_CD="/usr/bin/cd"
CMD_ECHO="/usr/bin/echo"
CMD_PDFLATEX="/usr/bin/pdflatex"
CMD_RM="/usr/bin/rm"

# check if correct number of arguments are given
if [ "$#" -ne 2 ]; then
    $CMD_ECHO "USAGE: $0 WORKING_DIR FILENAME_BASE"
    exit 100
fi

# apply arguments to variables
DIR=$1
FILENAME=$2
FILENAME_AUX="$FILENAME.aux"
FILENAME_PID="$FILENAME.pid"

# change to given directory
cd $DIR || exit 100

# store own PID; will later be used to check if script is still running
$CMD_ECHO $$ > "$FILENAME_PID"

# run pdflatex; store it's return code to be returned by this script
$CMD_PDFLATEX -interaction=nonstopmode -halt-on-error "$FILENAME" &>/dev/null
PDFLATEX_RETURN_CODE=$?

# some cleanup
$CMD_RM -rf "$FILENAME.aux"

# crucial for this script is the success of pdflatex – return it's return code here
exit $PDFLATEX_RETURN_CODE
