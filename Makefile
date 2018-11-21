all: quickbooks

quickbooks:
	docker build -t mashiox/oauth2-quickbooks .
