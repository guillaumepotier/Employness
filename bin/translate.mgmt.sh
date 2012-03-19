old_IFS=$IFS
IFS=$'\n'

totalMessages=0
transFound=0
transMissing=0
transDeprecated=0

messagesFile="./src/Employness/Resources/translations/translations.php"

renderFile="./bin/totalmessages.yml"
tempfile="./bin/messages.temp.yml"
missingFile="./bin/missingmessages.yml"
deprecatedFile="./bin/deprecatedmessages.yml"

allInTwig=`find src/ -type f -name '*.twig' -execdir egrep -o -- "\{\{ ?['\"][^|()}{]*\|[(trans) ^}]*\}\}" {} \; | sed "s/.*['\"]\(.*\)['\"][^'\"]*$/\1/"`
allInPhp=`find src/ -type f -name '*.php' -execdir egrep -o -- "->trans\([^)]*\)" {} \; | sed "s/.*['\"]\(.*\)['\"][^'\"]*$/\1/"`

function add_translation {
    echo $key >> $tempfile
}

function add_missing {
    echo $LINE >> $missingFile
}

function add_deprecated {
    echo $LINE >> $deprecatedFile
}

used=()
found=()
missing=()
deprecated=()

# Init by cleaning renderFile
rm -rf $renderFile;
rm -rf $deprecatedFile;
rm -rf $missingFile;

for key in $allInTwig
    do
        add_translation $key
    done

for key in $allInPhp
    do
        add_translation $key
    done

sort -u $tempfile >> $renderFile
rm -rf $tempfile

for LINE in `cat -u $renderFile`
    do
        if grep -ihq "$LINE" $messagesFile
        then
            found=("${found[@]}" "$LINE")
            let "transFound++"
        else
            missing=("${missing[@]}" "$LINE")
            add_missing $LINE
            let "transMissing++"
        fi

        let "totalMessages++"
    done

for LINE in `cat $messagesFile | egrep -o -- "([^=>#])* ?" | sed "s/.*['\"]\(.*\)['\"][^'\"]*$/\1/"`
    do
        if ! grep -ihq "$LINE" $renderFile
        then
            deprecated=("${deprecated[@]}" "$LINE")
            add_deprecated $LINE
            let "transDeprecated++"
        fi
    done

echo "Found $totalMessages total translations, $transFound found, $transMissing missing and $transDeprecated deprecated"