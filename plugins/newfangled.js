import jscookie from "js-cookie";
import axios from "axios";

export default ({ app: { router }, store }) => {
  /*
** Only run on client-side and only in production mode
*/
  if (process.env.NODE_ENV === "production") {
    router.afterEach((to, from) => {
      /*
      ** Send the pageview
      */
      var externalID;
      if (typeof ActOn !== "undefined" && ActOn && ActOn.Beacon) {
        ActOn.Beacon.track();

        if (typeof ActOn.Beacon.cookie !== "undefined") {
          externalID = ActOn.Beacon.cookie["4852"];
        }
      }

      var sessionId = jscookie.get("nfsession") || 0;
      var pageLink = window.location.href;
      var pageTitle = document.title ? document.title : pageLink;

      var params = {
        token: "da4596d42db619720f1d573329bd7c01de4e7f61",
        sessionid: sessionId,
        pagelink: to.fullPath,
        pagetitle: pageTitle,
        contentid: "",
        referrer: from.fullPath,
        urlroot: "bigduck.familiar.studio",
        utm_campaign: "",
        utm_content: "",
        utm_source: "",
        utm_medium: "",
        external_id: externalID,
        external_source: "acton"
      };
      try {
        axios
          .get("https://insight-engine.newfangled.com/api/v1/pagehit", {
            params: params
          })
          .then(response => {
            jscookie.set("nfsession", response.data, {
              expires: 365
            });
          })
          .catch(response => {
            console.error("tracking error", response);
          });
      } catch (exception) {
        console.error("tracking exception", exception);
      }
    });
  }
};
