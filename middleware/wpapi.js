import WPAPI from 'wpapi'

let wp = new WPAPI({ endpoint: 'http://bigducknyc.com/wp-json'});
wp.caseStudies = wp.registerRoute( 'wp/v2', '/bd_case_study')
wp.events = wp.registerRoute( 'wp/v2', '/bd_event')

export default function (context) {
  context.wp = wp
}
