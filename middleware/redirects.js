export default function({ route, redirect }) {
  const redirects = {
    "/professional_fundraising_marketing_and_logos_for_nonprofits": "/work"
  };

  console.log("path", route.path);
  if (redirects[route.path]) {
    return redirect(301, redirects[route.path]);
  }
}
