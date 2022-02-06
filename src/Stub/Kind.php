<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Stub;

/**
 * @see https://github.com/nikic/php-ast/blob/v1.0.16/ast_stub.php obtained by github
 * PHPDoc stub file for ast extension
 * @author Bill Schaller <bill@zeroedin.com>
 * @author Nikita Popov <nikic@php.net>
 */
class Kind
{
    const AST_ARG_LIST = 128;
    const AST_LIST = 255;
    const AST_ARRAY = 129;
    const AST_ENCAPS_LIST = 130;
    const AST_EXPR_LIST = 131;
    const AST_STMT_LIST = 132;
    const AST_IF = 133;
    const AST_SWITCH_LIST = 134;
    const AST_CATCH_LIST = 135;
    const AST_PARAM_LIST = 136;
    const AST_CLOSURE_USES = 137;
    const AST_PROP_DECL = 138;
    const AST_CONST_DECL = 139;
    const AST_CLASS_CONST_DECL = 140;
    const AST_NAME_LIST = 141;
    const AST_TRAIT_ADAPTATIONS = 142;
    const AST_USE = 143;
    const AST_TYPE_UNION = 144;
    const AST_TYPE_INTERSECTION = 145;
    const AST_ATTRIBUTE_LIST = 146;
    const AST_ATTRIBUTE_GROUP = 147;
    const AST_MATCH_ARM_LIST = 148;
    const AST_NAME = 2048;
    const AST_CLOSURE_VAR = 2049;
    const AST_NULLABLE_TYPE = 2050;
    const AST_FUNC_DECL = 67;
    const AST_CLOSURE = 68;
    const AST_METHOD = 69;
    const AST_ARROW_FUNC = 71;
    const AST_CLASS = 70;
    const AST_MAGIC_CONST = 0;
    const AST_TYPE = 1;
    const AST_CALLABLE_CONVERT = 3;
    const AST_VAR = 256;
    const AST_CONST = 257;
    const AST_UNPACK = 258;
    const AST_CAST = 261;
    const AST_EMPTY = 262;
    const AST_ISSET = 263;
    const AST_SHELL_EXEC = 265;
    const AST_CLONE = 266;
    const AST_EXIT = 267;
    const AST_PRINT = 268;
    const AST_INCLUDE_OR_EVAL = 269;
    const AST_UNARY_OP = 270;
    const AST_PRE_INC = 271;
    const AST_PRE_DEC = 272;
    const AST_POST_INC = 273;
    const AST_POST_DEC = 274;
    const AST_YIELD_FROM = 275;
    const AST_GLOBAL = 277;
    const AST_UNSET = 278;
    const AST_RETURN = 279;
    const AST_LABEL = 280;
    const AST_REF = 281;
    const AST_HALT_COMPILER = 282;
    const AST_ECHO = 283;
    const AST_THROW = 284;
    const AST_GOTO = 285;
    const AST_BREAK = 286;
    const AST_CONTINUE = 287;
    const AST_CLASS_NAME = 276;
    const AST_CLASS_CONST_GROUP = 546;
    const AST_DIM = 512;
    const AST_PROP = 513;
    const AST_NULLSAFE_PROP = 514;
    const AST_STATIC_PROP = 515;
    const AST_CALL = 516;
    const AST_CLASS_CONST = 517;
    const AST_ASSIGN = 518;
    const AST_ASSIGN_REF = 519;
    const AST_ASSIGN_OP = 520;
    const AST_BINARY_OP = 521;
    const AST_ARRAY_ELEM = 526;
    const AST_NEW = 527;
    const AST_INSTANCEOF = 528;
    const AST_YIELD = 529;
    const AST_STATIC = 532;
    const AST_WHILE = 533;
    const AST_DO_WHILE = 534;
    const AST_IF_ELEM = 535;
    const AST_SWITCH = 536;
    const AST_SWITCH_CASE = 537;
    const AST_DECLARE = 538;
    const AST_PROP_ELEM = 775;
    const AST_PROP_GROUP = 774;
    const AST_CONST_ELEM = 776;
    const AST_USE_TRAIT = 539;
    const AST_TRAIT_PRECEDENCE = 540;
    const AST_METHOD_REFERENCE = 541;
    const AST_NAMESPACE = 542;
    const AST_USE_ELEM = 543;
    const AST_TRAIT_ALIAS = 544;
    const AST_GROUP_USE = 545;
    const AST_ATTRIBUTE = 547;
    const AST_MATCH = 548;
    const AST_MATCH_ARM = 549;
    const AST_NAMED_ARG = 550;
    const AST_METHOD_CALL = 768;
    const AST_NULLSAFE_METHOD_CALL = 769;
    const AST_STATIC_CALL = 770;
    const AST_CONDITIONAL = 771;
    const AST_TRY = 772;
    const AST_CATCH = 773;
    const AST_FOR = 1024;
    const AST_FOREACH = 1025;
    const AST_ENUM_CASE = 1026;
    const AST_PARAM = 1280;
}
