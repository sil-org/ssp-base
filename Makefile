hub: clean deps jsdeps
	docker compose up -d ssp-hub.local

clean:
	docker compose kill
	docker compose rm -f

deps:
	docker compose run --rm composer bash -c "composer install --no-scripts --no-progress"

depsupdate:
	docker compose run --rm composer bash -c "./update-composer-deps.sh"

test:
	docker compose run --rm ssp-hub.local ./run-metadata-tests.sh
	docker compose run --rm ssp-idp1.local ./run-metadata-tests.sh
	docker compose run --rm ssp-sp1.local ./run-metadata-tests.sh
	docker compose run --rm test

test-integration:
	docker compose run --rm test ./run-integration-tests.sh

copyJsLib:
	cp ./node_modules/@simplewebauthn/browser/dist/bundle/index.umd.min.js ./modules/mfa/public/simplewebauthn/browser.js
	cp ./node_modules/@simplewebauthn/browser/LICENSE.md ./modules/mfa/public/simplewebauthn/LICENSE.md

jsdeps:
	docker compose run --rm node npm install --ignore-scripts
	make copyJsLib

jsdepsupdate:
	docker compose run --rm node npm update --ignore-scripts
	make copyJsLib

certs:
	db/make-db-cert.sh
