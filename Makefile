PHP=php7.2
HOST=localhost:9999
ROOT=.
FILE=server.php
VIZ=CS3744Visualization

.PHONY: dev-server

dev-server: FORCE public/js/companyViz.js
	$(PHP) -S $(HOST) -t $(ROOT) $(FILE)

public/js/companyViz.js: $(VIZ)/src
	cd $(VIZ) && npm run-script build
	cp $(VIZ)/build/static/js/main.*.js $@

FORCE: ;