{
  "lex": {

    "macros": {
      "identifier": "[_A-Za-z][_0-9A-Za-z]*"
    },

    "rules": [
      [ "\\s+", "/* skip whitespace */  " ],
      [ "\\`(?:[^\\`\\\\\\\\]|\\\\\\\\.)*\\`", "yytext = substr(yytext, 1, -1); return 'STRING'"],
      [ "-?(?:[0-9]+\\.[0-9]+|\\.[0-9]+)\\b", "yytext = floatval(yytext); return 'FLOAT'" ],
      [ "-?(?:[0-9]|[1-9][0-9]+)\\b", "yytext = intval(yytext); return 'INTEGER'" ],
      [ "\\(", "return '('" ],
      [ "\\)", "return ')'" ],
      [ "\\[", "return '['" ],
      [ "\\]", "return ']'" ],
      [ ",", "return ','" ],
      [ "\\.", "return '.'" ],
      [ ">=", "return 'GREATER_THAN_EQUALS'" ],
      [ "<=", "return 'LESS_THAN_EQUALS'" ],
      [ "!=", "return 'NOT_EQUALS'" ],
      [ "=", "return 'EQUALS'" ],
      [ ">", "return 'GREATER_THAN'" ],
      [ "<", "return 'LESS_THAN'" ],
      [ "and\\b", "return 'AND'" ],
      [ "or\\b", "return 'OR'" ],
      [ "not\\b", "return 'NOT'" ],
      [ "in\\b", "return 'IN'" ],
      [ "match\\b", "return 'MATCH'" ],
      [ "on\\b", "return 'ON'" ],
      [ "null\\b", "return 'NULL'" ],
      [ "true\\b", "return 'TRUE'" ],
      [ "false\\b", "return 'FALSE'" ],
      [ ":{identifier}\\(\\)", "yytext = trim(yytext, ':()'); return 'COMPARATOR'" ],
      [ ":{identifier}", "yytext = trim(yytext, ':'); return 'PARAMETER'" ],
      [ "{identifier}", "return 'IDENTIFIER'" ]
    ]
  },

  "operators": [
    [ "left", "OR" ],
    [ "left", "AND" ],
    [ "left", "NOT" ]
  ],

  "bnf": {
    "Expression": [
      [ "( Expression )", "$$ = $2" ],
      [ "Expression AND Expression", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' =>$3]" ],
      [ "Expression OR Expression", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' =>$3]" ],
      [ "NOT Expression", "$$ = ['type' => 'predicate', 'operator' => $1, 'left' => $2]" ],
      [ "Predicate", "$$ = $1" ]
    ],
    "Predicate": [
      [ "Path Operator Path", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' => $3]" ],
      [ "Path Operator Parameter", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' => $3]" ],
      [ "Path Comparator", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1]" ],
      [ "Path Operator Scalar", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' => $3]" ],
      [ "Path IN List", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' => $3]" ],
      [ "Path IN Parameter", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' => $3]" ],
      [ "Path MATCH Regex", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' => $3]" ],
      [ "Path MATCH Parameter", "$$ = ['type' => 'predicate', 'operator' => $2, 'left' => $1, 'right' => $3]" ]
    ],
    "Constraint": [
      [ "ON Type", "$$ = $2" ]
    ],
    "Comparator": [
      [ "COMPARATOR", "$$ = ['type' => 'comparator', 'name' => $1]" ]
    ],
    "Parameter": [
      [ "PARAMETER", "$$ = ['type' => 'parameter', 'name' => $1]" ]
    ],
    "Operator": [
      [ "EQUALS", "$$ = $1" ],
      [ "GREATER_THAN", "$$ = $1" ],
      [ "LESS_THAN", "$$ = $1" ],
      [ "GREATER_THAN_EQUALS", "$$ = $1" ],
      [ "LESS_THAN_EQUALS", "$$ = $1" ],
      [ "NOT_EQUALS", "$$ = $1" ]
    ],
    "List": [
      [ "[ ]", "$$ = ['type' => 'list', 'value' => []]" ],
      [ "[ Strings ]", "$$ = ['type' => 'list', 'value' => $2]" ],
      [ "[ Integers ]", "$$ = ['type' => 'list', 'value' => $2]" ],
      [ "[ Floats ]", "$$ = ['type' => 'list', 'value' => $2]" ]
    ],
    "Strings": [
      [ "STRING", "$$ = [$1]" ],
      [ "Strings , STRING", "$1[] = $3; $$ = $1" ],
      [ "Strings , NULL", "$1[] = $3; $$ = $1" ]
    ],
    "Integers": [
      [ "INTEGER", "$$ = [$1]" ],
      [ "Integers , INTEGER", "$1[] = $3; $$ = $1" ],
      [ "Integers , NULL", "$1[] = $3; $$ = $1" ]
    ],
    "Floats": [
      [ "FLOAT", "$$ = [$1]" ],
      [ "Floats , FLOAT", "$1[] = $3; $$ = $1" ],
      [ "Floats , NULL", "$1[] = $3; $$ = $1" ]
    ],
    "Scalar": [
      [ "NULL", "$$ = ['type' => 'none', 'value' => null]" ],
      [ "TRUE", "$$ = ['type' => 'boolean', 'value' => true]" ],
      [ "FALSE", "$$ = ['type' => 'boolean', 'value' => false]" ],
      [ "INTEGER", "$$ = ['type' => 'integer', 'value' => $1]" ],
      [ "FLOAT", "$$ = ['type' => 'float', 'value' => floatval($1)]" ],
      [ "STRING", "$$ = ['type' => 'string', 'value' => trim($1, '`')]" ]
    ],
    "Regex": [
      [ "STRING", "$$ = ['type' => 'regex', 'value' => $1]" ]
    ],
    "Path": [
      [ "Segments", "$$ = $1" ],
      [ "Segments Constraint", "$1['constraint'] = $2; $$ = $1" ]
    ],
    "Segments": [
      [ "IDENTIFIER", "$$ = ['type' => 'path', 'segments' => [$1]]" ],
      [ "Segments . IDENTIFIER", "$1['segments'][] = $3; $$ = $1" ]
    ],
    "Field": [
      [ "IDENTIFIER", "$$ = $1" ]
    ],
    "Type": [
      [ "IDENTIFIER", "$$ = $1" ]
    ]
  }
}