# Python is primarily used for our project provisioning

python:
  pkg.installed:
    - name: python-dev

python-mysqldb:
  pkg:
    - installed
  require:
    - pkg: python
