<?php

/**
 * LR parser generated by the Syntax tool.
 *
 * https://www.npmjs.com/package/syntax-cli
 *
 *   npm install -g syntax-cli
 *
 *   syntax-cli --help
 *
 * To regenerate run:
 *
 *   syntax-cli \
 *     --grammar ~/path-to-grammar-file \
 *     --mode <parsing-mode> \
 *     --output ~/ParserClassName.php
 */

namespace TYPO3\CMS\Core\GraphQL;

/**
 * Default exception for syntax errors.
 */
class SyntaxException extends \Exception {}

/**
 * `yy` is a storage which semantic actions may use to
 * store needed intermediate results or state, which can be
 * accessed accross semantic actions, and in the tokenizer.
 *
 * It also exposes access to the tokenizer, so semantic actions
 * can change its state.
 */
final class yy {
  /**
   * Tokenizer instance.
   */
  public static $tokenizer = null;

  /**
   * Alias of the tokenizer instance.
   */
  public static $lexer = null;

  /**
   * User-level storage.
   */
  private static $storage = array();

  public function set($name, $value) {
    self::$storage[$name] = $value;
  }

  public function get($name) {
    return self::$storage[$name];
  }
}



/**
 * Base class for all generated LR parsers.
 */
class yyparse {

  /**
   * Productions table (generated by Syntax tool).
   *
   * Format of a row:
   *
   * [ <NonTerminal Index>, <RHS.length>, <semanticActionName> ]
   */
  private static $productions = [[-1,1,'_handler1'],
[0,3,'_handler2'],
[0,3,'_handler3'],
[0,3,'_handler4'],
[0,2,'_handler5'],
[0,1,'_handler6'],
[1,3,'_handler7'],
[1,3,'_handler8'],
[1,2,'_handler9'],
[1,3,'_handler10'],
[1,3,'_handler11'],
[1,3,'_handler12'],
[1,3,'_handler13'],
[1,3,'_handler14'],
[2,2,'_handler15'],
[3,1,'_handler16'],
[4,1,'_handler17'],
[5,1,'_handler18'],
[5,1,'_handler19'],
[5,1,'_handler20'],
[5,1,'_handler21'],
[5,1,'_handler22'],
[5,1,'_handler23'],
[6,2,'_handler24'],
[6,3,'_handler25'],
[6,3,'_handler26'],
[6,3,'_handler27'],
[7,1,'_handler28'],
[7,3,'_handler29'],
[7,3,'_handler30'],
[8,1,'_handler31'],
[8,3,'_handler32'],
[8,3,'_handler33'],
[9,1,'_handler34'],
[9,3,'_handler35'],
[9,3,'_handler36'],
[10,1,'_handler37'],
[10,1,'_handler38'],
[10,1,'_handler39'],
[10,1,'_handler40'],
[10,1,'_handler41'],
[10,1,'_handler42'],
[11,1,'_handler43'],
[12,1,'_handler44'],
[12,2,'_handler45'],
[13,1,'_handler46'],
[13,3,'_handler47'],
[14,1,'_handler48'],
[15,1,'_handler49']];

  /**
   * Tokens map (from token type to encoded index, autogenerated).
   */
  private static $tokens = array('(' => "16", ')' => "17", 'AND' => "18", 'OR' => "19", 'NOT' => "20", 'IN' => "21", 'MATCH' => "22", 'ON' => "23", 'COMPARATOR' => "24", 'PARAMETER' => "25", 'EQUALS' => "26", 'GREATER_THAN' => "27", 'LESS_THAN' => "28", 'GREATER_THAN_EQUALS' => "29", 'LESS_THAN_EQUALS' => "30", 'NOT_EQUALS' => "31", '[' => "32", ']' => "33", 'STRING' => "34", ',' => "35", 'NULL' => "36", 'INTEGER' => "37", 'FLOAT' => "38", 'TRUE' => "39", 'FALSE' => "40", 'IDENTIFIER' => "41", '.' => "42", '$' => "43");

  /**
   * Parsing table (generated by Syntax tool).
   */
  private static $table = array(array('0' => 1, '1' => 4, '12' => 5, '13' => 6, '16' => "s2", '20' => "s3", '41' => "s7"), array('18' => "s8", '19' => "s9", '43' => "acc"), array('0' => 12, '1' => 4, '12' => 5, '13' => 6, '16' => "s2", '20' => "s3", '41' => "s7"), array('0' => 14, '1' => 4, '12' => 5, '13' => 6, '16' => "s2", '20' => "s3", '41' => "s7"), array('17' => "r5", '18' => "r5", '19' => "r5", '43' => "r5"), array('3' => 16, '5' => 15, '21' => "s17", '22' => "s18", '24' => "s25", '26' => "s19", '27' => "s20", '28' => "s21", '29' => "s22", '30' => "s23", '31' => "s24"), array('2' => 36, '17' => "r43", '18' => "r43", '19' => "r43", '21' => "r43", '22' => "r43", '23' => "s38", '24' => "r43", '26' => "r43", '27' => "r43", '28' => "r43", '29' => "r43", '30' => "r43", '31' => "r43", '42' => "s37", '43' => "r43"), array('17' => "r45", '18' => "r45", '19' => "r45", '21' => "r45", '22' => "r45", '23' => "r45", '24' => "r45", '26' => "r45", '27' => "r45", '28' => "r45", '29' => "r45", '30' => "r45", '31' => "r45", '42' => "r45", '43' => "r45"), array('0' => 10, '1' => 4, '12' => 5, '13' => 6, '16' => "s2", '20' => "s3", '41' => "s7"), array('0' => 11, '1' => 4, '12' => 5, '13' => 6, '16' => "s2", '20' => "s3", '41' => "s7"), array('17' => "r2", '18' => "r2", '19' => "r2", '43' => "r2"), array('17' => "r3", '18' => "s8", '19' => "r3", '43' => "r3"), array('17' => "s13", '18' => "s8", '19' => "s9"), array('17' => "r1", '18' => "r1", '19' => "r1", '43' => "r1"), array('17' => "r4", '18' => "r4", '19' => "r4", '43' => "r4"), array('4' => 27, '10' => 28, '12' => 26, '13' => 6, '25' => "s29", '34' => "s35", '36' => "s30", '37' => "s33", '38' => "s34", '39' => "s31", '40' => "s32", '41' => "s7"), array('17' => "r8", '18' => "r8", '19' => "r8", '43' => "r8"), array('4' => 43, '6' => 42, '25' => "s29", '32' => "s44"), array('4' => 65, '11' => 64, '25' => "s29", '34' => "s66"), array('25' => "r17", '34' => "r17", '36' => "r17", '37' => "r17", '38' => "r17", '39' => "r17", '40' => "r17", '41' => "r17"), array('25' => "r18", '34' => "r18", '36' => "r18", '37' => "r18", '38' => "r18", '39' => "r18", '40' => "r18", '41' => "r18"), array('25' => "r19", '34' => "r19", '36' => "r19", '37' => "r19", '38' => "r19", '39' => "r19", '40' => "r19", '41' => "r19"), array('25' => "r20", '34' => "r20", '36' => "r20", '37' => "r20", '38' => "r20", '39' => "r20", '40' => "r20", '41' => "r20"), array('25' => "r21", '34' => "r21", '36' => "r21", '37' => "r21", '38' => "r21", '39' => "r21", '40' => "r21", '41' => "r21"), array('25' => "r22", '34' => "r22", '36' => "r22", '37' => "r22", '38' => "r22", '39' => "r22", '40' => "r22", '41' => "r22"), array('17' => "r15", '18' => "r15", '19' => "r15", '43' => "r15"), array('17' => "r6", '18' => "r6", '19' => "r6", '43' => "r6"), array('17' => "r7", '18' => "r7", '19' => "r7", '43' => "r7"), array('17' => "r9", '18' => "r9", '19' => "r9", '43' => "r9"), array('17' => "r16", '18' => "r16", '19' => "r16", '43' => "r16"), array('17' => "r36", '18' => "r36", '19' => "r36", '43' => "r36"), array('17' => "r37", '18' => "r37", '19' => "r37", '43' => "r37"), array('17' => "r38", '18' => "r38", '19' => "r38", '43' => "r38"), array('17' => "r39", '18' => "r39", '19' => "r39", '43' => "r39"), array('17' => "r40", '18' => "r40", '19' => "r40", '43' => "r40"), array('17' => "r41", '18' => "r41", '19' => "r41", '43' => "r41"), array('17' => "r44", '18' => "r44", '19' => "r44", '21' => "r44", '22' => "r44", '24' => "r44", '26' => "r44", '27' => "r44", '28' => "r44", '29' => "r44", '30' => "r44", '31' => "r44", '43' => "r44"), array('41' => "s39"), array('15' => 40, '41' => "s41"), array('17' => "r46", '18' => "r46", '19' => "r46", '21' => "r46", '22' => "r46", '23' => "r46", '24' => "r46", '26' => "r46", '27' => "r46", '28' => "r46", '29' => "r46", '30' => "r46", '31' => "r46", '42' => "r46", '43' => "r46"), array('17' => "r14", '18' => "r14", '19' => "r14", '21' => "r14", '22' => "r14", '24' => "r14", '26' => "r14", '27' => "r14", '28' => "r14", '29' => "r14", '30' => "r14", '31' => "r14", '43' => "r14"), array('17' => "r48", '18' => "r48", '19' => "r48", '21' => "r48", '22' => "r48", '24' => "r48", '26' => "r48", '27' => "r48", '28' => "r48", '29' => "r48", '30' => "r48", '31' => "r48", '43' => "r48"), array('17' => "r10", '18' => "r10", '19' => "r10", '43' => "r10"), array('17' => "r11", '18' => "r11", '19' => "r11", '43' => "r11"), array('7' => 46, '8' => 47, '9' => 48, '33' => "s45", '34' => "s49", '37' => "s50", '38' => "s51"), array('17' => "r23", '18' => "r23", '19' => "r23", '43' => "r23"), array('33' => "s52", '35' => "s53"), array('33' => "s56", '35' => "s57"), array('33' => "s60", '35' => "s61"), array('33' => "r27", '35' => "r27"), array('33' => "r30", '35' => "r30"), array('33' => "r33", '35' => "r33"), array('17' => "r24", '18' => "r24", '19' => "r24", '43' => "r24"), array('34' => "s54", '36' => "s55"), array('33' => "r28", '35' => "r28"), array('33' => "r29", '35' => "r29"), array('17' => "r25", '18' => "r25", '19' => "r25", '43' => "r25"), array('36' => "s59", '37' => "s58"), array('33' => "r31", '35' => "r31"), array('33' => "r32", '35' => "r32"), array('17' => "r26", '18' => "r26", '19' => "r26", '43' => "r26"), array('36' => "s63", '38' => "s62"), array('33' => "r34", '35' => "r34"), array('33' => "r35", '35' => "r35"), array('17' => "r12", '18' => "r12", '19' => "r12", '43' => "r12"), array('17' => "r13", '18' => "r13", '19' => "r13", '43' => "r13"), array('17' => "r42", '18' => "r42", '19' => "r42", '43' => "r42"));

  /**
   * Parsing stack.
   */
  private static $stack = [];

  /**
   * Result of a semantic action (used as `$$`).
   */
  private static $__ = null;

  /**
   * Result location (used as `@$`).
   */
  private static $__loc = null;

  /**
   * Parser event callbacks.
   */
  private static $on_parse_begin = null;
  private static $on_parse_end = null;

  /**
   * Matched token text.
   */
  public static $yytext = '';

  /**
   * Matched token length.
   */
  public static $yyleng = 0;

  /**
   * End of file symbol.
   */
  const EOF = '$';

  /**
   * Tokenizer instance.
   */
  private static $tokenizer = null;

  private static function _handler1($_1) {
yyparse::$__ = $_1;
}

private static function _handler2($_1,$_2,$_3) {
yyparse::$__ = $_2;
}

private static function _handler3($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' =>$_3];
}

private static function _handler4($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' =>$_3];
}

private static function _handler5($_1,$_2) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_1, 'left' => $_2];
}

private static function _handler6($_1) {
yyparse::$__ = $_1;
}

private static function _handler7($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' => $_3];
}

private static function _handler8($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' => $_3];
}

private static function _handler9($_1,$_2) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1];
}

private static function _handler10($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' => $_3];
}

private static function _handler11($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' => $_3];
}

private static function _handler12($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' => $_3];
}

private static function _handler13($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' => $_3];
}

private static function _handler14($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'predicate', 'operator' => $_2, 'left' => $_1, 'right' => $_3];
}

private static function _handler15($_1,$_2) {
yyparse::$__ = $_2;
}

private static function _handler16($_1) {
yyparse::$__ = ['type' => 'comparator', 'name' => $_1];
}

private static function _handler17($_1) {
yyparse::$__ = ['type' => 'parameter', 'name' => $_1];
}

private static function _handler18($_1) {
yyparse::$__ = $_1;
}

private static function _handler19($_1) {
yyparse::$__ = $_1;
}

private static function _handler20($_1) {
yyparse::$__ = $_1;
}

private static function _handler21($_1) {
yyparse::$__ = $_1;
}

private static function _handler22($_1) {
yyparse::$__ = $_1;
}

private static function _handler23($_1) {
yyparse::$__ = $_1;
}

private static function _handler24($_1,$_2) {
yyparse::$__ = ['type' => 'list', 'value' => []];
}

private static function _handler25($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'list', 'value' => $_2];
}

private static function _handler26($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'list', 'value' => $_2];
}

private static function _handler27($_1,$_2,$_3) {
yyparse::$__ = ['type' => 'list', 'value' => $_2];
}

private static function _handler28($_1) {
yyparse::$__ = [$_1];
}

private static function _handler29($_1,$_2,$_3) {
$_1[] = $_3; yyparse::$__ = $_1;
}

private static function _handler30($_1,$_2,$_3) {
$_1[] = $_3; yyparse::$__ = $_1;
}

private static function _handler31($_1) {
yyparse::$__ = [$_1];
}

private static function _handler32($_1,$_2,$_3) {
$_1[] = $_3; yyparse::$__ = $_1;
}

private static function _handler33($_1,$_2,$_3) {
$_1[] = $_3; yyparse::$__ = $_1;
}

private static function _handler34($_1) {
yyparse::$__ = [$_1];
}

private static function _handler35($_1,$_2,$_3) {
$_1[] = $_3; yyparse::$__ = $_1;
}

private static function _handler36($_1,$_2,$_3) {
$_1[] = $_3; yyparse::$__ = $_1;
}

private static function _handler37($_1) {
yyparse::$__ = ['type' => 'none', 'value' => null];
}

private static function _handler38($_1) {
yyparse::$__ = ['type' => 'boolean', 'value' => true];
}

private static function _handler39($_1) {
yyparse::$__ = ['type' => 'boolean', 'value' => false];
}

private static function _handler40($_1) {
yyparse::$__ = ['type' => 'integer', 'value' => $_1];
}

private static function _handler41($_1) {
yyparse::$__ = ['type' => 'float', 'value' => floatval($_1)];
}

private static function _handler42($_1) {
yyparse::$__ = ['type' => 'string', 'value' => trim($_1, '`')];
}

private static function _handler43($_1) {
yyparse::$__ = ['type' => 'regex', 'value' => $_1];
}

private static function _handler44($_1) {
yyparse::$__ = $_1;
}

private static function _handler45($_1,$_2) {
$_1['constraint'] = $_2; yyparse::$__ = $_1;
}

private static function _handler46($_1) {
yyparse::$__ = ['type' => 'path', 'segments' => [$_1]];
}

private static function _handler47($_1,$_2,$_3) {
$_1['segments'][] = $_3; yyparse::$__ = $_1;
}

private static function _handler48($_1) {
yyparse::$__ = $_1;
}

private static function _handler49($_1) {
yyparse::$__ = $_1;
}

  private static $shouldCaptureLocations = false;

  private static function yyloc($start, $end) {
    // Epsilon doesn't produce location.
    if (!$start || !$end) {
      return !$start ? $end : $static;
    }

    return array(
      'startOffset' => $start['startOffset'],
      'endOffset' => $end['endOffset'],
      'startLine' => $start['startLine'],
      'endLine' => $end['endLine'],
      'startColumn' => $start['startColumn'],
      'endColumn' => $end['endColumn'],
    );
  }

  public static function setTokenizer($tokenizer) {
    self::$tokenizer = $tokenizer;

    // Also set it on `yy` so semantic actions can access the tokenizer.
    yy::$tokenizer = $tokenizer;
    yy::$lexer = $tokenizer;
  }

  public static function getTokenizer() {
    return self::$tokenizer;
  }

  public static function setOnParseBegin($on_parse_begin) {
    self::$on_parse_begin = $on_parse_begin;
  }

  public static function setOnParseEnd($on_parse_end) {
    self::$on_parse_end = $on_parse_end;
  }

  public static function parse($string) {
    if (self::$on_parse_begin) {
      $on_parse_begin = self::$on_parse_begin;
      $on_parse_begin($string);
    }

    $tokenizer = self::getTokenizer();

    if (!$tokenizer) {
      throw new SyntaxException(`Tokenizer is not provided.`);
    }

    $tokenizer->initString($string);

    $stack = &self::$stack;
    $stack = ['0'];

    $tokens = &self::$tokens;
    $table = &self::$table;
    $productions = &self::$productions;

    $token = $tokenizer->getNextToken();
    $shifted_token = null;

    do {
      if (!$token) {
        self::unexpectedEndOfInput();
      }

      $state = end($stack);
      $column = $tokens[$token['type']];

      if (!isset($table[$state][$column])) {
        self::unexpectedToken($token);
      }
      $entry = $table[$state][$column];

      if ($entry[0] === 's') {
        $loc = null;

        if (self::$shouldCaptureLocations) {
          $loc = array(
            'startOffset' => $token['startOffset'],
            'endOffset'=> $token['endOffset'],
            'startLine' => $token['startLine'],
            'endLine' => $token['endLine'],
            'startColumn' => $token['startColumn'],
            'endColumn' => $token['endColumn'],
          );
        }

        array_push(
          $stack,
          array(
            'symbol' => $tokens[$token['type']],
            'semanticValue' => $token['value'],
            'loc' => $loc,
          ),
          intval(substr($entry, 1))
        );
        $shifted_token = $token;
        $token = $tokenizer->getNextToken();
      } else if ($entry[0] === 'r') {
        $production_number = intval(substr($entry, 1));
        $production = $productions[$production_number];
        $has_semantic_action = count($production) > 2;
        $semantic_value_args = $has_semantic_action ? [] : null;

        $location_args = (
          $has_semantic_action && self::$shouldCaptureLocations
            ? []
            : null
        );

        if ($production[1] !== 0) {
          $rhs_length = $production[1];
          while ($rhs_length-- > 0) {
            array_pop($stack);
            $stack_entry = array_pop($stack);

            if ($has_semantic_action) {
              array_unshift(
                $semantic_value_args,
                $stack_entry['semanticValue']
              );

              if ($location_args !== null) {
                array_unshift(
                  $location_args,
                  $stack_entry['loc']
                );
              }
            }
          }
        }

        $reduce_stack_entry = array('symbol' => $production[0]);

        if ($has_semantic_action) {
          self::$yytext = $shifted_token ? $shifted_token['value'] : null;
          self::$yyleng = $shifted_token ? strlen($shifted_token['value']) : null;

          forward_static_call_array(
            array('self', $production[2]),
            $location_args !== null
              ? array_merge($semantic_value_args, $location_args)
              : $semantic_value_args
          );

          $reduce_stack_entry['semanticValue'] = self::$__;

          if ($location_args !== null) {
            $reduce_stack_entry['loc'] = self::$__loc;
          }
        }

        $next_state = end($stack);
        $symbol_to_reduce_with = $production[0];

        array_push(
          $stack,
          $reduce_stack_entry,
          $table[$next_state][$symbol_to_reduce_with]
        );
      } else if ($entry === 'acc') {
        array_pop($stack);
        $parsed = array_pop($stack);

        if (count($stack) !== 1 ||
            $stack[0] !== '0' ||
            $tokenizer->hasMoreTokens()) {
          self::unexpectedToken($token);
        }

        $parsed_value = array_key_exists('semanticValue', $parsed)
          ? $parsed['semanticValue']
          : true;

        if (self::$on_parse_end) {
          $on_parse_end = self::$on_parse_end;
          $on_parse_end($parsed_value);
        }

        return $parsed_value;
      }

    } while ($tokenizer->hasMoreTokens() || count($stack) > 1);
  }

  private static function unexpectedToken($token) {
    if ($token['type'] === self::EOF) {
      self::unexpectedEndOfInput();
    }

    self::getTokenizer()->throwUnexpectedToken(
      $token['value'],
      $token['startLine'],
      $token['startColumn']
    );
  }

  private static function unexpectedEndOfInput() {
    self::parseError('Unexpected end of input.');
  }

  private static function parseError($message) {
    throw new SyntaxException('SyntaxError: ' . $message);
  }
}


/**
 * Generic tokenizer used by the parser in the Syntax tool.
 *
 * https://www.npmjs.com/package/syntax-cli
 *
 * See `--custom-tokinzer` to skip this generation, and use a custom one.
 */

class Tokenizer {
  private static $lexRules = [['/^\s+/', '_lex_rule1'],
['/^\`(?:[^\`\\\\]|\\\\.)*\`/', '_lex_rule2'],
['/^-?(?:[0-9]+\.[0-9]+|\.[0-9]+)\b/', '_lex_rule3'],
['/^-?(?:[0-9]|[1-9][0-9]+)\b/', '_lex_rule4'],
['/^\(/', '_lex_rule5'],
['/^\)/', '_lex_rule6'],
['/^\[/', '_lex_rule7'],
['/^\]/', '_lex_rule8'],
['/^,/', '_lex_rule9'],
['/^\./', '_lex_rule10'],
['/^>=/', '_lex_rule11'],
['/^<=/', '_lex_rule12'],
['/^!=/', '_lex_rule13'],
['/^=/', '_lex_rule14'],
['/^>/', '_lex_rule15'],
['/^</', '_lex_rule16'],
['/^and\b/', '_lex_rule17'],
['/^or\b/', '_lex_rule18'],
['/^not\b/', '_lex_rule19'],
['/^in\b/', '_lex_rule20'],
['/^match\b/', '_lex_rule21'],
['/^on\b/', '_lex_rule22'],
['/^null\b/', '_lex_rule23'],
['/^true\b/', '_lex_rule24'],
['/^false\b/', '_lex_rule25'],
['/^:[_A-Za-z][_0-9A-Za-z]*\(\)/', '_lex_rule26'],
['/^:[_A-Za-z][_0-9A-Za-z]*/', '_lex_rule27'],
['/^[_A-Za-z][_0-9A-Za-z]*/', '_lex_rule28']];
  private static $lexRulesByConditions = array('INITIAL' => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27));

  private $states = array();
  private $string = '';
  private $stringLen = 0;
  private $cursor = 0;
  private $tokensQueue = array();

  /**
   * Line-based location tracking.
   */
  private $currentLine = 1;
  private $currentColumn = 0;
  private $currentLineBeginOffset = 0;

  /**
   * Location data of a matched token.
   */
  private $tokenStartOffset = 0;
  private $tokenEndOffset = 0;
  private $tokenStartLine = 0;
  private $tokenEndLine = 0;
  private $tokenStartColumn = 0;
  private $tokenEndColumn = 0;

  private static $EOF_TOKEN = array(
    'type' => yyparse::EOF,
    'value' => '',
  );

  private function _lex_rule1() {
/* skip whitespace */  ;
}

private function _lex_rule2() {
yyparse::$yytext = substr(yyparse::$yytext, 1, -1); return 'STRING';
}

private function _lex_rule3() {
yyparse::$yytext = floatval(yyparse::$yytext); return 'FLOAT';
}

private function _lex_rule4() {
yyparse::$yytext = intval(yyparse::$yytext); return 'INTEGER';
}

private function _lex_rule5() {
return '(';
}

private function _lex_rule6() {
return ')';
}

private function _lex_rule7() {
return '[';
}

private function _lex_rule8() {
return ']';
}

private function _lex_rule9() {
return ',';
}

private function _lex_rule10() {
return '.';
}

private function _lex_rule11() {
return 'GREATER_THAN_EQUALS';
}

private function _lex_rule12() {
return 'LESS_THAN_EQUALS';
}

private function _lex_rule13() {
return 'NOT_EQUALS';
}

private function _lex_rule14() {
return 'EQUALS';
}

private function _lex_rule15() {
return 'GREATER_THAN';
}

private function _lex_rule16() {
return 'LESS_THAN';
}

private function _lex_rule17() {
return 'AND';
}

private function _lex_rule18() {
return 'OR';
}

private function _lex_rule19() {
return 'NOT';
}

private function _lex_rule20() {
return 'IN';
}

private function _lex_rule21() {
return 'MATCH';
}

private function _lex_rule22() {
return 'ON';
}

private function _lex_rule23() {
return 'NULL';
}

private function _lex_rule24() {
return 'TRUE';
}

private function _lex_rule25() {
return 'FALSE';
}

private function _lex_rule26() {
yyparse::$yytext = trim(yyparse::$yytext, ':()'); return 'COMPARATOR';
}

private function _lex_rule27() {
yyparse::$yytext = trim(yyparse::$yytext, ':'); return 'PARAMETER';
}

private function _lex_rule28() {
return 'IDENTIFIER';
}

  public function initString($string) {
    $this->string = $string;
    $this->stringLen = strlen($this->string);
    $this->cursor = 0;
    $this->states = array('INITIAL');
    $this->tokensQueue = array();

    $this->currentLine = 1;
    $this->currentColumn = 0;
    $this->currentLineBeginOffset = 0;

    /**
     * Location data of a matched token.
     */
    $this->tokenStartOffset = 0;
    $this->tokenEndOffset = 0;
    $this->tokenStartLine = 0;
    $this->tokenEndLine = 0;
    $this->tokenStartColumn = 0;
    $this->tokenEndColumn = 0;
  }

  public function getStates() {
    return $this->states;
  }

  public function getCurrentState() {
    return $this->states[count($this->states) - 1];
  }

  public function pushState($state) {
    $this->states[] = $state;
  }

  public function begin($state) {
    $this->pushState(state);
  }

  public function popState() {
    if (count($this->states) > 1) {
      return array_pop($this->states);
    }
    return $this->states[0];
  }

  public function getNextToken() {
    if (count($this->tokensQueue) > 0) {
      return $this->toToken(array_shift($this->tokensQueue));
    }

    if (!$this->hasMoreTokens()) {
      return self::$EOF_TOKEN;
    }

    $string = substr($this->string, $this->cursor);
    $lexRulesForState = static::$lexRulesByConditions[$this->getCurrentState()];

    foreach ($lexRulesForState as $lex_rule_index) {
      $lex_rule = self::$lexRules[$lex_rule_index];

      $matched = $this->match($string, $lex_rule[0]);

      // Manual handling of EOF token (the end of string). Return it
      // as `EOF` symbol.
      if (!$string && $matched === '') {
        $this->cursor++;
      }

      if ($matched !== null) {
        yyparse::$yytext = $matched;
        yyparse::$yyleng = strlen($matched);
        $token = call_user_func(array($this, $lex_rule[1]));
        if (!$token) {
          return $this->getNextToken();
        }

        // If multiple tokens are returned, save them to return
        // on next `getNextToken` call.
        if (is_array($token)) {
          $tokens_to_queue = array_slice($token, 1);
          $token = $token[0];
          if (count($tokens_to_queue) > 0) {
            array_unshift($this->tokensQueue, ...$tokens_to_queue);
          }
        }

        return $this->toToken($token, yyparse::$yytext);
      }
    }

    if ($this->isEOF()) {
      $this->cursor++;
      return self::$EOF_TOKEN;
    }

    $this->throwUnexpectedToken(
      $string[0],
      $this->currentLine,
      $this->currentColumn
    );
  }

  /**
   * Throws default "Unexpected token" exception, showing the actual
   * line from the source, pointing with the ^ marker to the bad token.
   * In addition, shows `line:column` location.
   */
  public function throwUnexpectedToken($symbol, $line, $column) {
    $line_source = explode("\n", $this->string)[$line - 1];

    $pad = str_repeat(' ', $column);
    $line_data = "\n\n" . $line_source . "\n" . $pad . "^\n";

    throw new SyntaxException(
      $line_data . 'Unexpected token: "' . $symbol . '" at ' .
      $line . ':' . $column . '.'
    );
  }

  private function captureLocation($matched) {
    // Absolute offsets.
    $this->tokenStartOffset = $this->cursor;

    // Line-based locations, start.
    $this->tokenStartLine = $this->currentLine;
    $this->tokenStartColumn = $this->tokenStartOffset - $this->currentLineBeginOffset;

    // Extract `\n` in the matched token.
    preg_match_all('/\n/', $matched, $nl_matches, PREG_OFFSET_CAPTURE);
    $nl_match = $nl_matches[0];

    if (count($nl_match) > 0) {
      foreach ($nl_match as $nl_match_data) {
        $this->currentLine++;
        // Offset is at index 1.
        $this->currentLineBeginOffset = $this->tokenStartOffset +
          $nl_match_data[1] + 1;
      }
    }

    $this->tokenEndOffset = $this->cursor + strlen($matched);

    // Line-based locations, end.
    $this->tokenEndLine = $this->currentLine;
    $this->tokenEndColumn = $this->currentColumn =
      ($this->tokenEndOffset - $this->currentLineBeginOffset);
  }

  private function toToken($token, $yytext = '') {
    return array(
      'type' => $token,
      'value' => $yytext,
      'startOffset' => $this->tokenStartOffset,
      'endOffset' => $this->tokenEndOffset,
      'startLine' => $this->tokenStartLine,
      'endLine' => $this->tokenEndLine,
      'startColumn' => $this->tokenStartColumn,
      'endColumn' => $this->tokenEndColumn,
    );
  }

  public function isEOF() {
    return $this->cursor == $this->stringLen;
  }

  public function hasMoreTokens() {
    return $this->cursor <= $this->stringLen;
  }

  private function match($string, $regexp) {
    preg_match($regexp, $string, $matches);
    if (count($matches) > 0) {
      $matched = $matches[0];
      $this->captureLocation($matched);
      $this->cursor += strlen($matched);
      return $matched;
    }
    return null;
  }
}

yyparse::setTokenizer(new Tokenizer());


class FilterParser extends yyparse {}
