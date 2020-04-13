Base64 = require('js-base64').Base64


# data = JSON.parse (window.__omen_data)
data = JSON.parse Base64.decode(window.__omen_data)


module.exports = {
    config: data[0],
    inodes: data[1]
}