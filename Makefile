# ==============================================================================
# General Commands
# ==============================================================================

.PHONY: help
help: ## ❓ Display this help screen.
	@printf "\033[33mUsage:\033[0m\n make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m\n"
	@grep -E '^[-a-zA-Z0-9_\.\/]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-32s\033[0m %s\n", $$1, $$2}'

# ==============================================================================
# Coding Style
# ==============================================================================

.PHONY: coding-style
coding-style: coding-style/check ## 👮 Alias to check for coding style violations.

.PHONY: coding-style/check
coding-style/check: coding-style/check/php-cs-fixer coding-style/check/rector ## 👀 Check for violations using all coding style tools (dry-run).

.PHONY: coding-style/check/mago
coding-style/check/mago: ## -- 🧐 Preview Mago fmt changes without applying them.
	vendor/bin/mago fmt --dry-run

.PHONY: coding-style/check/rector
coding-style/check/rector: ## -- 🧐 Preview Rector refactorings without applying them.
	vendor/bin/rector process --dry-run

.PHONY: coding-style/format
coding-style/format: coding-style/format/rector coding-style/format/mago ## ✨ Automatically fix all coding style violations.

.PHONY: coding-style/format/mago
coding-style/format/mago: ## -- 🎨 Automatically apply Mago fixes.
	vendor/bin/mago fmt

.PHONY: coding-style/format/rector
coding-style/format/rector: ## -- 🛠️ Automatically apply Rector refactorings.
	vendor/bin/rector process

# ==============================================================================
# Static Analysis
# ==============================================================================

.PHONY: static-analysis
static-analysis: static-analysis/mago static-analysis/phpstan ## 🔎 Run all static analysis tools.

.PHONY: static-analysis/phpstan
static-analysis/phpstan: ## -- 🐛 Analyze code for potential bugs using PHPStan.
	vendor/bin/phpstan analyse --memory-limit=-1

.PHONY: static-analysis/mago
static-analysis/mago: ## -- 🐛 Analyze code for potential bugs using Mago.
	vendor/bin/mago lint
	vendor/bin/mago analyse

# ==============================================================================
# Testing
# ==============================================================================

.PHONY: tests
tests: tests/unit ## ✅ Run all test suites.

.PHONY: tests/unit
tests/unit: ## -- 🧪 Run PHPUnit tests.
	XDEBUG_MODE=coverage vendor/bin/phpunit