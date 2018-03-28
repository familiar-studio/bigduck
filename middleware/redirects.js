export default function({ route, redirect }) {
  const redirects = {
    "/professional_fundraising_marketing_and_logos_for_nonprofits": "/work",
    "/nonprofit_marketing_and_fundraising_clients": "/about",
    "/nonprofit_brand_marketing_strategy_and_logo_design": "/services/brands",
    "/online_fundraising_and_awareness_campaigns_for_non_profits":
      "/services/campaigns",
    "/non_profit_marketing_and_fundraising_training": "/services/teams",
    "/nonprofit_training": "/events",
    "/nonprofit_training_webinars": "/insights",
    "/nonprofit_fundraising_and_marketing_blog": "/insights",
    "/nonprofit-communications-jobs": "/about",
    "/big-duck-company-values": "/about",
    "/team": "/about",
    "/contact": "/contact-us",
    "/workshops": "/events",
    "/clients": "/about#clients",
    "/blog": "/insights",
    "/casestudies": "/work/all",
    "/rebrandeffect": "/insights/the-rebrand-effect",
    "/effective_communications_and_fundraising_for_nonprofits": "/services",
    "/we-believe": "/about",
    "/brandraising-benchmark": "/services/brandraising-benchmark",
    "/the-power-of-a-simple-elevator-pitch":
      "/insights/three-signs-you-need-an-elevator-pitch-three-ways-to-get-started",
    "/atlanta-community-food-bank-rebrand":
      "/work/a-stronger-brand-for-a-great-new-strategic-plan",
    "/good-shepherd-nyc-human-services-branding": "/work/all",
    "/brooklyn-community-foundation-brand": "/work/all",
    "/branding-to-boost-enrollment-at-art-school": "/work/all",
    "/sarah-durham": "/about/sarah-durham",
    "/theresa-gutierrez": "/about/theresa-gutierrez",
    "/insights/address_directions": "/directions",
    "/podcasts": "/insights?type=21"
  };

  // console.log("path", route.path);
  if (redirects[route.path]) {
    return redirect(301, redirects[route.path]);
  }
}
