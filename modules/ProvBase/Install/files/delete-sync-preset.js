'use strict';

const axios = require('/lib/node_modules/axios/dist/node/axios.cjs');

function deleteSyncPreset(args, callback) {
    axios.delete("http://127.0.0.1:7557/presets/sync-" + args)
    callback(null, null);
}

exports.ret = deleteSyncPreset;
