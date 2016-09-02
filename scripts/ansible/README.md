## Download packages

`ansible-galaxy install -r requirements.yml`

## Run that bad boy

`ansible-playbook setup.yml -i inventories/production --vault-password-file pass.txt`

## If you need to change secret vars

`ansible-vault group_vars/webservers edit --vault-password-file pass.txt`
