/**
 * ThePreloader.js
 * A lightweight preloader plugin.
 * 
 * @author    Alobaidi
 * @version   1.0.0
 */

class ThePreloader {
    constructor(options = {}) {
        this.options = {
            preloaderElement: typeof options.preloaderElement === 'string' && options.preloaderElement.trim() 
                ? options.preloaderElement.toLowerCase().trim() 
                : 'the-preloader-element'
        };

        this.preloader = document.getElementById(this.options.preloaderElement);

        this.init = this.init.bind(this);
        this.hidePreloader = this.hidePreloader.bind(this);
    }

    fadeOut(element, duration) {
        return new Promise(resolve => {
            element.style.transition = `opacity ${duration}s ease-in-out`;
            element.style.opacity = '0';
            
            setTimeout(() => {
                element.style.display = 'none';
                resolve();
            }, duration * 1000 + 50);
        });
    }

    async hidePreloader() {
        await new Promise(resolve => 
            setTimeout(resolve, 500)
        );

        await this.fadeOut(this.preloader, 0.5);
        
        setTimeout(() => {
            this.preloader.remove();
        }, 50);
    }

    init() {
        if ( !this.preloader ) {
            console.log('ThePreloader.js: Preloader element does not exist.');
            return;
        }

        this.hidePreloader();
    }
}

document.addEventListener('DOMContentLoaded', function() {

    window.addEventListener('load', () => {
        const the_Preloader = new ThePreloader({
            preloaderElement: 'the-preloader-element'
        });
        the_Preloader.init();
    });

});