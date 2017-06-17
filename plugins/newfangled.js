import jscookie from 'js-cookie'
import axios from 'axios'

export default ({app: {router}, store}) => {
/*
** Only run on client-side and only in production mode
*/
  if (process.env.NODE_ENV === 'production') {
    router.afterEach((to, from) => {
      /*
      ** Send the pageview
      */
      ActOn.Beacon.track()

      var sessionId = jscookie.get('nfsession') || 0
      var pageLink = window.location.href
      var pageTitle = document.title ? document.title : pageLink
      var externalID
      if (typeof ActOn.Beacon.cookie !== 'undefined') {
        externalID = ActOn.Beacon.cookie['4852']
      }
      var params = {
        token: 'da4596d42db619720f1d573329bd7c01de4e7f61',
        sessionid: sessionId,
        pagelink: to.fullPath,
        pagetitle: pageTitle,
        contentid: '',
        referrer: from.fullPath,
        urlroot: 'bigduck.familiar.studio',
        utm_campaign: '',
        utm_content: '',
        utm_source: '',
        utm_medium: '',
        external_id: externalID,
        external_source: 'acton'
      }

      axios.get('https://insight-engine.newfangled.com/api/v1/pagehit', { params: params }).then((response) => {
        console.log('page view', response)
        Cookies.set('nfsession', response.data, { expires: 7 })
      })
    })
  }
}
