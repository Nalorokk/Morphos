<?php
namespace morphos\Russian;

use morphos\S;

/**
 * Rules are from http://gramma.ru/SPR/?id=2.8
 */
class LastNamesInflection extends \morphos\NamesInflection implements Cases
{
    use RussianLanguage, CasesHelper;

    protected static $menPostfixes = array('ов', 'ев' ,'ин' ,'ын', 'ой', 'ий');
    protected static $womenPostfixes = array('ва', 'на', 'ая', 'яя');

    public static function isMutable($name, $gender = null)
    {
        $name = S::lower($name);
        if ($gender === null) {
            $gender = self::detectGender($name);
        }
        // составная фамилия - разбить на части и проверить по отдельности
        if (strpos($name, '-') !== false) {
            foreach (explode('-', $name) as $part) {
                if (static::isMutable($part, $gender))
                    return true;
            }
            return false;
        }

        if (in_array(S::slice($name, -1), array('а', 'я'))) {
            return true;
        }

        if ($gender == self::MALE) {
            // Несклоняемые фамилии (Фоминых, Седых / Стецко, Писаренко)
            if (in_array(S::slice($name, -2), ['ых', 'ко']))
                return false;

            // Несклоняемые, образованные из родительного падежа личного или прозвищного имени главы семьи
            // суффиксы: ово, аго
            if (in_array(S::slice($name, -3), ['ово', 'аго']))
                return false;

            // Типичные суффикс мужских фамилий
            if (in_array(S::slice($name, -2), array('ов', 'ев', 'ин', 'ын', 'ий', 'ой'))) {
                return true;
            }

            // Согласная на конце
            if (self::isConsonant(S::slice($name, -1))) {
                return true;
            }

            // Мягкий знак на конце
            if (S::slice($name, -1) == 'ь') {
                return true;
            }

        } else {
            // Типичные суффиксы женских фамилий
            if (in_array(S::slice($name, -2), ['ва', 'на', 'ая'])) {
                return true;
            }
        }

        return false;
    }

    public static function detectGender($name)
    {
        $name = S::lower($name);
        if (in_array(S::slice($name, -2), self::$menPostfixes)) {
            return self::MALE;
        }
        if (in_array(S::slice($name, -2), self::$womenPostfixes)) {
            return self::FEMALE;
        }

        return null;
    }

    public static function getCases($name, $gender = null)
    {
        $name = S::lower($name);
        if ($gender === null) {
            $gender = self::detectGender($name);
        }

        // составная фамилия - разбить на части и склонять по отдельности
        if (strpos($name, '-') !== false) {
            $parts = explode('-', $name);
            $cases = [];
            foreach ($parts as $i => $part) {
                $parts[$i] = static::getCases($part, $gender);
            }

            return self::composeCasesFromWords($parts, '-');
        }
        if (!static::isMutable($name, $gender)) {
            $prefix = S::name($name);
            return array(
                self::IMENIT    => $prefix,
                self::RODIT     => $prefix,
                self::DAT       => $prefix,
                self::VINIT     => $prefix,
                self::TVORIT    => $prefix,
                self::PREDLOJ   => $prefix
            );
        }

        if ($gender == self::MALE) {
            if (in_array(S::slice($name, -2), array('ов', 'ев', 'ин', 'ын'))) {
                $prefix = S::name($name);
                return array(
                    self::IMENIT => $prefix,
                    self::RODIT => $prefix.'а',
                    self::DAT => $prefix.'у',
                    self::VINIT => $prefix.'а',
                    self::TVORIT => $prefix.'ым',
                    self::PREDLOJ => $prefix.'е'
                );
            } elseif (in_array(S::slice($name, -4), array('ский', 'ской', 'цкий', 'цкой'))) {
                $prefix = S::name(S::slice($name, 0, -2));
                return array(
                    self::IMENIT => S::name($name),
                    self::RODIT => $prefix.'ого',
                    self::DAT => $prefix.'ому',
                    self::VINIT => $prefix.'ого',
                    self::TVORIT => $prefix.'им',
                    self::PREDLOJ => $prefix.'ом'
                );
            // Верхний / Убогий / Толстой
            // Верхнего / Убогого / Толстого
            // Верхнему / Убогому / Толстому
            // Верхним / Убогим / Толстым
            // О Верхнем / Об Убогом / О Толстом
            } else if (in_array(S::slice($name, -2), ['ой', 'ый', 'ий'])) {
                $prefix = S::name(S::slice($name, 0, -2));
                return array(
                    self::IMENIT => S::name($name),
                    self::RODIT => $prefix.'ого',
                    self::DAT => $prefix.'ому',
                    self::VINIT => $prefix.'ого',
                    self::TVORIT => $prefix.'ым',
                    self::PREDLOJ => $prefix.'ом'
                );
            }

        } else {
            if (in_array(S::slice($name, -3), array('ова', 'ева', 'ина', 'ына'))) {
                $prefix = S::name(S::slice($name, 0, -1));
                return array(
                    self::IMENIT => S::name($name),
                    self::RODIT => $prefix.'ой',
                    self::DAT => $prefix.'ой',
                    self::VINIT => $prefix.'у',
                    self::TVORIT => $prefix.'ой',
                    self::PREDLOJ => $prefix.'ой'
                );
            }

            if (in_array(S::slice($name, -2), array('ая'))) {
                $prefix = S::name(S::slice($name, 0, -2));
                return array(
                    self::IMENIT => S::name($name),
                    self::RODIT => $prefix.'ой',
                    self::DAT => $prefix.'ой',
                    self::VINIT => $prefix.'ую',
                    self::TVORIT => $prefix.'ой',
                    self::PREDLOJ => $prefix.'ой'
                );
            }
        }

        if (S::slice($name, -1) == 'я') {
            $prefix = S::name(S::slice($name, 0, -1));
            return array(
                self::IMENIT => S::name($name),
                self::RODIT => $prefix.'и',
                self::DAT => $prefix.'е',
                self::VINIT => $prefix.'ю',
                self::TVORIT => $prefix.'ей',
                self::PREDLOJ => $prefix.'е'
            );
        } elseif (S::slice($name, -1) == 'а') {
            $prefix = S::name(S::slice($name, 0, -1));
            return array(
                self::IMENIT => S::name($name),
                self::RODIT => $prefix.(self::isDeafConsonant(S::slice($name, -2, -1)) ? 'и' : 'ы'),
                self::DAT => $prefix.'е',
                self::VINIT => $prefix.'у',
                self::TVORIT => $prefix.'ой',
                self::PREDLOJ => $prefix.'е'
            );
        } elseif (self::isConsonant(S::slice($name, -1)) && S::slice($name, -2) != 'ых') {
            $prefix = S::name($name);
            return array(
                self::IMENIT => S::name($name),
                self::RODIT => $prefix.'а',
                self::DAT => $prefix.'у',
                self::VINIT => $prefix.'а',
                self::TVORIT => $prefix.'ом',
                self::PREDLOJ => $prefix.'е'
            );
        } elseif (S::slice($name, -1) == 'ь' && $gender == self::MALE) {
            $prefix = S::name(S::slice($name, 0, -1));
            return array(
                self::IMENIT => S::name($name),
                self::RODIT => $prefix.'я',
                self::DAT => $prefix.'ю',
                self::VINIT => $prefix.'я',
                self::TVORIT => $prefix.'ем',
                self::PREDLOJ => $prefix.'е'
            );
        }

        $name = S::name($name);
        return array_fill_keys(array(self::IMENIT, self::RODIT, self::DAT, self::VINIT, self::TVORIT, self::PREDLOJ), $name);
    }

    public static function getCase($name, $case, $gender = null)
    {
        if (!static::isMutable($name, $gender)) {
            return $name;
        } else {
            $case = self::canonizeCase($case);
            $forms = self::getCases($name, $gender);
            return $forms[$case];
        }
    }
}
