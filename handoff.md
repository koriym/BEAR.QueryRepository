# Handoff: PR #254 (qr-graph) レビュー対応の継続

## これは何

bearsunday/BEAR.QueryRepository#254 "Add qr-graph: the cache graph the attributes declare"
(head `ef58d9d`, base `1.x`) に対するレビュー結果と、対応方針の引き継ぎ。
このファイルは作業用メモ。**merge 前にブランチから削除すること**(PR diff に残さない)。

## 状態

- PR 本体のコードは `src-graph/` (namespace `BEAR\QueryRepositoryGraph`)、CLI は `bin/qr-graph`、
  テスト `tests/QrGraphTest.php`、スキーマ `docs/schemas/graph/{dump,impact}.json`。
- 動作検証は済み: QrGraphTest 11 tests / 173 assertions OK。dump 116 行はスキーマ検証をパス。
  Tokenizer+Reflection による独自 ground truth と dump は 0 mismatch(暗黙 refresh ルールも
  ランタイム実装と一致: Cacheable→RefreshSameCommand、CacheableResponse→
  DonutCommandInterceptor.refreshDonutAndState)。
- フルテストの 7 errors / 112 failures はベースブランチでも同じ(ローカル vendor の
  semantic-logger 0.9.0 vs 要求 ^0.11 が原因)。PR 由来の退行ではない。
- phpcs / phpstan / psalm は src-graph についてクリーン。

## レビュー確定事項(優先度順)

### 1. PACKAGING (blocker) — merge 前に意思決定必須

`composer.json` が prod `autoload` に `BEAR\QueryRepositoryGraph\` を置き、
`"bin": ["bin/qr-graph"]` で全 consumer の `vendor/bin` に dev ツールを配布している。

**重要**: 「autoload-dev に移す」回避策は成立しない。依存パッケージの autoload-dev は
root プロジェクトにしか適用されないため、consumer からクラスが見えず、残った bin が fatal になる。
選択肢は二択:

- (推奨) 別パッケージ化(`bear/query-repository-graph` など)し、アプリ側は require-dev で導入。
  PR body と docs/graph.md:12-14 が既に "planned to move to a separate package" と言っているので、
  リリース後に動かすと公開面の BC break になる前に今やる。
- 割り切って prod に出すなら、docs から「移動予定」の記述を消し、公開面として明示する。

リポトリ内の最小 diff(別パッケージ化とセット):

```diff
  "autoload": { "psr-4": {
      "BEAR\\QueryRepository\\": ["src/", "src-deprecated/"],
      "BEAR\\RepositoryModule\\Annotation\\": ["src-annotation/", "src-annotation-deprecated/"],
-     "BEAR\\QueryRepositoryGraph\\": "src-graph/"
  }},
- "bin": ["bin/qr-graph"],
```

### 2. SCANNER CONTRACT (should-fix)

`src-graph/ResourceScanner.php:83-87` の正規表現スキャンの問題:

- 1 行に `#[Cacheable] class X extends ResourceObject` と書かれたファイルは `className()` が
  null を返し `continue` で**無診断 drop**。docs/graph.md:25 の「autoloader が解決しないファイルは
  stderr に skipped と報告」の契約違反。
- braced namespace (`namespace Foo { class X }`) は namespace 正規表現が `;` 終端のみ
  マッチするため "X is not autoloadable" と誤診断。
- 複数クラス/ファイルは先頭クラスのみスキャン(classmap 未対応)。

推奨: `token_get_all()` (ext-tokenizer は PHP core、新規依存ではない) による
namespace/class 抽出に置き換え、解決不能ファイルは必ず `skipped` に出す。

### 3. UX: silent impact (should-fix)

- `src-graph/Impact.php:54`: `impact <uri> --method=<未知 or 非 write>` は空配列にフォールバックし
  **無出力 exit 0**。検証ツールで typo が「影響なし」に見えるのは危険。
  最小修正: `--method` 指定時に `$this->nodes[$uri]['methods'][$method]` が存在しなければ
  JSONL error を stderr に出し exit 1。
- `src-graph/Cli.php:48-60`: 未知の `--flag` が positional arg に黙って落ちる。
  最小修正: `--` で始まる未知オプションは usage を出して exit 2。

### 4. DOC/IMPL MISMATCH (nit)

docs/graph.md:26 と `Cli.php:25` docblock は「diagnostics は全て JSONL」と主張するが、
`Cli.php:62-63` の usage エラーと "No such directory" は平文。
docs 側を「usage errors are plain text」と修正するのが最小。

### 5. STALE DOCBLOCK (nit)

`src-graph/GraphBuilder.php:36`: `docs/schemas/graph/resource.json` → 実体は `dump.json`。

### 6. COVERAGE BLIND SPOT (nit)

`phpunit.xml.dist` の `<source>` は `src` のみ。Codecov の 100% は src-graph を語らない。
`<directory suffix=".php">src-graph</directory>` を追加。

### 7. Minor (nit)

- `tests/QrGraphTest.php` は `JsonSchema\Validator` を使うが `justinrainbow/json-schema` は
  require-dev に未宣言(bear/resource 経由の推移依存)。require-dev に明示。
- `bin/qr-graph` は autoloader が 1 つも見つからない場合にクリーンなエラーでなく
  `Class Cli not found` fatal になる。
- Impact の depth 意味論が不統一: `--method` 起点は depth 1、URI 起点は depth 0。
  schema か docs で明確化。
- composer `metrics` スクリプトが src-graph を対象外(任意)。

## 補足(対応不要だが知っておく)

- `dynamic` ヒントは fake app で 13/115 リソースが `surrogate-key` を持つ。`ResourceInterface`
  正規表現は use 文/type-hint だけでも発火しうる(false positive)。docs は
  "absence proves nothing" と謙虚なので許容範囲。
- phpcs.xml:20 に src-graph は含まれている(bin/qr-graph が対象外なのは gen_og.php と同じ慣行)。

## 作業環境

- PR worktree: `/tmp/qr254` (このブランチ = PR head `ef58d9d` + この handoff)。依存インストール済み。
- ベース checkout: `/Users/akihito/git/BEAR.QueryRepository` (1.x)。
- ランタイム ground truth: `src/CommandInterceptor.php`, `src/RefreshSameCommand.php`,
  `src/DonutCommandInterceptor.php`, `src/CacheableModule.php`, `src/DonutCacheModule.php`,
  `src/RefreshAnnotatedCommand.php`。属性は `src-annotation/`。
- 検証コマンド: `cd /tmp/qr254 && ./vendor/bin/phpunit --filter QrGraphTest`、
  `php bin/qr-graph dump --dir=tests/Fake/fake-app/src/Resource`。

## 完了条件

1. Packaging の方針決定(別パッケージ化 or prod 同梱の割り切り)と反映。
2. Finding 2, 3 (should-fix) の修正。
3. Finding 4-7 (nit) の修正。任意だがコストは低い。
4. QrGraphTest 全パス + phpcs/phpstan/psalm クリーンを維持。
5. この handoff.md を削除してから PR ブランチへ反映。
