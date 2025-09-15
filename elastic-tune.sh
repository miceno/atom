
# This script allows tunning the watermark of elasticsearch nodes that
# run in docker environments.
# The script disables the watermark check.
#

curl -XGET -H "Content-Type: application/json" http://localhost:63002/_cat/indices

curl -XPUT -H "Content-Type: application/json" http://localhost:63002/_cluster/settings -d '{ "transient": { "cluster.routing.allocation.disk.threshold_enabled": false } }'
curl -XPUT -H "Content-Type: application/json" http://localhost:63002/_all/_settings -d '{"index.blocks.read_only_allow_delete": null}'
