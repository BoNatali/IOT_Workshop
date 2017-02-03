<!doctype html>
<title>Public API Help</title>
<h1>Parameters</h1>
<p>You can use the following parameters in a querystring:</p>
<table>
  <thead>
    <th>Parameter</th>
    <th>Datatype</th>
    <th>Description</th>
  </thead>
  <tbody>
    <tr>
      <td>t</td>
      <td>string</td>
      <td>Type of action</td>
    </tr>
    <tr>
      <td>d</td>
      <td>string</td>
      <td>Device id</td>
    </tr>
    <tr>
      <td>td</td>
      <td>string</td>
      <td>Target device id</td>
    </tr>
    <tr>
      <td>c</td>
      <td>string</td>
      <td>Color, hex is default, change cv if you want to use another value</td>
    </tr>
    <tr>
      <td>cv</td>
      <td>string</td>
      <td>Color value type (hex or hue)</td>
    </tr>
    <tr>
      <td>sc</td>
      <td>int</td>
      <td>Spring constant</td>
    </tr>
    <tr>
      <td>dc</td>
      <td>int</td>
      <td>Damp constant</td>
    </tr>
    <tr>
      <td>m</td>
      <td>string</td>
      <td>Message, can be anything</td>
    </tr>
    <tr>
      <td>v</td>
      <td>int</td>
      <td>Version, should be two if you have the latest firmware</td>
    </tr>
  </tbody>
</table>
<h1>Actions</h1>
<p>Type of actions (t):</p>
<table>
  <thead>
    <th>Key</th>
    <th>Description</th>
    <th>Parameters</th>
    <th>Output</th>
  </thead>
  <tbody>
    <tr>
      <td>sdc</td>
      <td>Set device configuration</td>
      <td>d*, td*, c*, cv, sc, dc, m</td>
      <td>1 or -1</td>
    </tr>
    <tr>
      <td>rdc</td>
      <td>Remove device configuration</td>
      <td>d*, td*</td>
      <td>1 or -1</td>
    </tr>
    <tr>
      <td>gqi</td>
      <td>Get query item</td>
      <td>d*, v</td>
      <td>hex_color(,spring_constant,damp_constant,message)</td>
    </tr>
    <tr>
      <td>sqi</td>
      <td>Set query item</td>
      <td>d*</td>
      <td>1 or -1</td>
    </tr>
  </tbody>
  <tfoot>
    * is required
  </tfoot>
</table>
<h1>Examples</h1>
<table>
  <tr>
    <td>api.php?t=sdc&d=T111&td=T222&c=ff0000</td>
    <td>Sets the device configuration of T111 for T222 with the hex color ff0000</td>
  </tr>
  <tr>
    <td>api.php?t=gqi&d=T111&v=2</td>
    <td>Gets the next queue item for T111, v=2 means that it will not only return the color but also the spring constant, damp constant and message of the queue item</td>
  </tr>
</table>
