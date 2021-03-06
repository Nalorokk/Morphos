<?php
namespace morphos\Russian;

use morphos\S;

/**
 * Rules are from http://surnameonline.ru/patronymic.html
 */
class MiddleNamesInflection extends \morphos\NamesInflection implements Cases
{
    use RussianLanguage, CasesHelper;

    public static function detectGender($name)
    {
        $name = S::lower($name);
        if (S::slice($name, -2) == 'ич') {
            return self::MALE;
        } elseif (S::slice($name, -2) == 'на') {
            return self::FEMALE;
        }

        return null;
    }

    public static function isMutable($name, $gender = null)
    {
        $name = S::lower($name);
        if (in_array(S::slice($name, -2), array('ич', 'на'))) {
            return true;
        }
        // if foreign, try it as a name
        return FirstNamesInflection::isMutable($name, $gender);
    }

    public static function getCase($name, $case, $gender = null)
    {
        $case = self::canonizeCase($case);
        $forms = self::getCases($name, $gender);
        return $forms[$case];
    }

    public static function getCases($name, $gender = null)
    {
        $name = S::lower($name);
        if (S::slice($name, -2) == 'ич') {
            // man rules
            $name = S::name($name);
            return array(
                Cases::IMENIT => $name,
                Cases::RODIT => $name.'а',
                Cases::DAT => $name.'у',
                Cases::VINIT => $name.'а',
                Cases::TVORIT => $name.'ем',
                Cases::PREDLOJ => $name.'е',
            );
        } elseif (S::slice($name, -2) == 'на') {
            $prefix = S::name(S::slice($name, 0, -1));
            return array(
                Cases::IMENIT => $prefix.'а',
                Cases::RODIT => $prefix.'ы',
                Cases::DAT => $prefix.'е',
                Cases::VINIT => $prefix.'у',
                Cases::TVORIT => $prefix.'ой',
                Cases::PREDLOJ => $prefix.'е',
            );
        }

        // inflect other middle names as first names (foreign)
        return FirstNamesInflection::getCases($name, $gender);
    }
}
