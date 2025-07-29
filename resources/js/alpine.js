import sort from '@alpinejs/sort'

window.addEventListener('alpine:init', () => {
    window.Alpine.plugin(sort)
})

require('../../vendor/wire-elements/modal/resources/js/modal')
